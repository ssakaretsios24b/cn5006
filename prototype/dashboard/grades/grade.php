<?php
/**
 * Βαθμολόγηση Υποβολής
 * Μόνο για καθηγητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireTeacher();

$db = getDB();
$submissionId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε την υποβολή και ελέγχουμε ότι ανήκει σε μάθημα του καθηγητή
$stmt = $db->prepare('
    SELECT s.*, u.username, u.email, a.title as assignment_title, a.description as assignment_desc,
           c.name as course_name, c.teacher_id
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE s.id = ?
');
$stmt->execute([$submissionId]);
$submission = $stmt->fetch();

if (!$submission || $submission['teacher_id'] != $userId) {
    setFlash('error', 'Η υποβολή δεν βρέθηκε ή δεν έχεις δικαίωμα.');
    redirect(url('dashboard/grades/'));
}

$pageTitle = 'Βαθμολόγηση - ' . $submission['assignment_title'];
$currentPage = 'grades';

$errors = [];

if (isPost()) {
    $grade = post('grade');

    // Validation
    if ($grade === '') {
        $errors[] = 'Ο βαθμός είναι υποχρεωτικός';
    } else {
        $grade = (float) $grade;
        if ($grade < 0 || $grade > 10) {
            $errors[] = 'Ο βαθμός πρέπει να είναι μεταξύ 0 και 10';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('
                UPDATE submissions
                SET grade = ?, graded_at = NOW(), graded_by = ?
                WHERE id = ?
            ');
            $stmt->execute([$grade, $userId, $submissionId]);

            setFlash('success', 'Ο βαθμός καταχωρήθηκε επιτυχώς!');
            redirect(url('dashboard/grades/'));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/grades/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στις Βαθμολογίες
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star me-2"></i>Βαθμολόγηση Υποβολής</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Πληροφορίες υποβολής -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Φοιτητής</h6>
                        <p class="mb-2">
                            <strong><?= e($submission['username']) ?></strong><br>
                            <small class="text-muted"><?= e($submission['email']) ?></small>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Υποβλήθηκε</h6>
                        <p class="mb-2"><?= formatDate($submission['submitted_at']) ?></p>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4">
                    <h6><?= e($submission['assignment_title']) ?></h6>
                    <p class="text-muted mb-0">
                        Μάθημα: <?= e($submission['course_name']) ?>
                    </p>
                </div>

                <!-- Αρχείο υποβολής -->
                <?php if ($submission['file_path']): ?>
                    <div class="mb-4">
                        <h6>Υποβληθέν Αρχείο</h6>
                        <a href="<?= url(
                            'uploads/' . basename((string) $submission['file_path']),
                        ) ?>" target="_blank"
                           class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>
                            <?= e(basename($submission['file_path'])) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Σχόλια φοιτητή -->
                <?php if ($submission['comments']): ?>
                    <div class="mb-4">
                        <h6>Σχόλια Φοιτητή</h6>
                        <div class="bg-white border p-3 rounded">
                            <?= nl2br(e($submission['comments'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <hr>

                <!-- Φόρμα βαθμολόγησης -->
                <form method="POST" class="needs-loading">
                    <?= csrfField() ?>

                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <label for="grade" class="form-label">Βαθμός (0-10) *</label>
                            <input type="number" class="form-control form-control-lg" id="grade" name="grade"
                                   min="0" max="10" step="0.5"
                                   value="<?= e($submission['grade'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-1"></i>
                                <?= $submission['grade'] !== null
                                    ? 'Ενημέρωση'
                                    : 'Καταχώρηση' ?> Βαθμού
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($submission['grade'] !== null): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Τρέχων βαθμός: <strong><?= number_format(
                            $submission['grade'],
                            1,
                        ) ?></strong>
                        (<?= formatDate($submission['graded_at']) ?>)
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Εργασία</div>
            <div class="card-body">
                <h6><?= e($submission['assignment_title']) ?></h6>
                <?php if ($submission['assignment_desc']): ?>
                    <p class="text-muted small">
                        <?= e(substr($submission['assignment_desc'], 0, 200)) ?>
                        <?= strlen($submission['assignment_desc']) > 200 ? '...' : '' ?>
                    </p>
                <?php endif; ?>
                <a href="<?= url(
                    'dashboard/assignments/view.php?id=' . $submission['assignment_id'],
                ) ?>"
                   class="btn btn-outline-primary btn-sm">
                    Προβολή Εργασίας
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
