<?php
/**
 * Επεξεργασία Εργασίας
 * Μόνο για τον καθηγητή που τη δημιούργησε
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireTeacher();

$db = getDB();
$assignmentId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε την εργασία
$stmt = $db->prepare('
    SELECT a.*, c.teacher_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
');
$stmt->execute([$assignmentId, $userId]);
$assignment = $stmt->fetch();

if (!$assignment) {
    setFlash('error', 'Η εργασία δεν βρέθηκε ή δεν έχεις δικαίωμα.');
    redirect(url('dashboard/assignments/'));
}

// Μαθήματα του καθηγητή
$stmt = $db->prepare('SELECT id, name FROM courses WHERE teacher_id = ? ORDER BY name');
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

$pageTitle = 'Επεξεργασία: ' . $assignment['title'];
$currentPage = 'assignments';

$errors = [];
$old = [
    'course_id' => $assignment['course_id'],
    'title' => $assignment['title'],
    'description' => $assignment['description'],
    'due_date' => $assignment['due_date'] ? date('Y-m-d', strtotime($assignment['due_date'])) : '',
    'due_time' => $assignment['due_date'] ? date('H:i', strtotime($assignment['due_date'])) : '',
];

if (isPost()) {
    $action = post('action');

    if ($action === 'delete') {
        try {
            $stmt = $db->prepare('DELETE FROM assignments WHERE id = ?');
            $stmt->execute([$assignmentId]);

            setFlash('success', 'Η εργασία διαγράφηκε.');
            redirect(url('dashboard/assignments/'));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα διαγραφής: ' . $e->getMessage();
        }
    } else {
        $courseId = (int) post('course_id');
        $title = post('title');
        $description = post('description');
        $dueDate = post('due_date');
        $dueTime = post('due_time');

        $old = compact('title', 'description');
        $old['course_id'] = $courseId;
        $old['due_date'] = $dueDate;
        $old['due_time'] = $dueTime;

        if (empty($title)) {
            $errors[] = 'Ο τίτλος είναι υποχρεωτικός';
        }

        if (empty($courseId)) {
            $errors[] = 'Επίλεξε μάθημα';
        } else {
            // Έλεγχος αν το μάθημα ανήκει στον καθηγητή
            $stmt = $db->prepare('SELECT id FROM courses WHERE id = ? AND teacher_id = ?');
            $stmt->execute([$courseId, $userId]);
            if (!$stmt->fetch()) {
                $errors[] = 'Μη έγκυρο μάθημα';
            }
        }

        $dueDatetime = null;
        if (!empty($dueDate)) {
            $dueDatetime = $dueDate . ' ' . ($dueTime ?: '23:59:59');
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare('
                    UPDATE assignments
                    SET course_id = ?, title = ?, description = ?, due_date = ?
                    WHERE id = ?
                ');
                $stmt->execute([$courseId, $title, $description, $dueDatetime, $assignmentId]);

                setFlash('success', 'Η εργασία ενημερώθηκε!');
                redirect(url('dashboard/assignments/'));
            } catch (PDOException $e) {
                $errors[] = 'Σφάλμα: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/assignments/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Επεξεργασία Εργασίας</h5>
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

                <form method="POST" class="needs-loading">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="course_id" class="form-label">Μάθημα *</label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>"
                                    <?= $old['course_id'] == $course['id'] ? 'selected' : '' ?>>
                                    <?= e($course['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= e($old['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="5"><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Προθεσμία</label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                   value="<?= e($old['due_date']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">Ώρα</label>
                            <input type="time" class="form-control" id="due_time" name="due_time"
                                   value="<?= e($old['due_time'] ?: '23:59') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Αποθήκευση
                    </button>
                    <a href="<?= url(
                        'dashboard/assignments/',
                    ) ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                </form>
            </div>
        </div>

        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>Διαγραφή
            </div>
            <div class="card-body">
                <p class="text-muted">Θα διαγραφούν και όλες οι υποβολές.</p>
                <form method="POST" onsubmit="return confirm('Είσαι σίγουρος;');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Διαγραφή
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
