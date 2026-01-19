<?php
/**
 * Προβολή Εργασίας
 * Καθηγητές βλέπουν υποβολές
 * Φοιτητές βλέπουν την εργασία και την υποβολή τους
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireLogin();

$db = getDB();
$assignmentId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε την εργασία
$stmt = $db->prepare('
    SELECT a.*, c.name as course_name, c.teacher_id, u.username as teacher_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON c.teacher_id = u.id
    WHERE a.id = ?
');
$stmt->execute([$assignmentId]);
$assignment = $stmt->fetch();

if (!$assignment) {
    setFlash('error', 'Η εργασία δεν βρέθηκε.');
    redirect(url('dashboard/assignments/'));
}

$isOwner = isTeacher() && $assignment['teacher_id'] == $userId;

// Έλεγχος πρόσβασης για φοιτητές
if (isStudent()) {
    $stmt = $db->prepare('
        SELECT id FROM enrollments
        WHERE course_id = (SELECT course_id FROM assignments WHERE id = ?)
        AND student_id = ?
    ');
    $stmt->execute([$assignmentId, $userId]);
    if (!$stmt->fetch()) {
        showForbidden();
    }
}

// Για καθηγητή: υποβολές
$submissions = [];
if ($isOwner) {
    $stmt = $db->prepare('
        SELECT s.*, u.username, u.email
        FROM submissions s
        JOIN users u ON s.student_id = u.id
        WHERE s.assignment_id = ?
        ORDER BY s.submitted_at DESC
    ');
    $stmt->execute([$assignmentId]);
    $submissions = $stmt->fetchAll();
}

// Για φοιτητή: η δική του υποβολή
$mySubmission = null;
if (isStudent()) {
    $stmt = $db->prepare('SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?');
    $stmt->execute([$assignmentId, $userId]);
    $mySubmission = $stmt->fetch();
}

$isPast = isPastDue($assignment['due_date']);

$pageTitle = $assignment['title'] . ' - Archimedes University';
$currentPage = 'assignments';

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/assignments/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στις Εργασίες
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Πληροφορίες εργασίας -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= e($assignment['title']) ?></h5>
                <?php if ($isPast): ?>
                    <span class="badge bg-danger">Έληξε</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Μάθημα</small>
                        <p class="mb-0">
                            <a href="<?= url(
                                'dashboard/courses/view.php?id=' . $assignment['course_id'],
                            ) ?>">
                                <?= e($assignment['course_name']) ?>
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Προθεσμία</small>
                        <p class="mb-0 <?= $isPast ? 'text-danger' : '' ?>">
                            <?= $assignment['due_date']
                                ? formatDate($assignment['due_date'])
                                : 'Χωρίς προθεσμία' ?>
                        </p>
                    </div>
                </div>

                <?php if ($assignment['description']): ?>
                    <hr>
                    <h6>Περιγραφή / Οδηγίες</h6>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(e($assignment['description'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isStudent()): ?>
            <!-- Υποβολή φοιτητή -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Η Υποβολή μου</h5>
                </div>
                <div class="card-body">
                    <?php if ($mySubmission): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-check-circle me-2"></i>
                            Έχεις υποβάλει στις <?= formatDate($mySubmission['submitted_at']) ?>
                        </div>

                        <?php if ($mySubmission['file_path']): ?>
                            <p>
                                <strong>Αρχείο:</strong>
                                <a href="<?= url(
                                    'uploads/' . basename((string) $mySubmission['file_path']),
                                ) ?>" target="_blank">
                                    <?= e(basename($mySubmission['file_path'])) ?>
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if ($mySubmission['comments']): ?>
                            <p><strong>Σχόλια:</strong> <?= e($mySubmission['comments']) ?></p>
                        <?php endif; ?>

                        <?php if ($mySubmission['grade'] !== null): ?>
                            <hr>
                            <h6>Βαθμολογία</h6>
                            <span class="badge bg-<?= gradeColor($mySubmission['grade']) ?> fs-4">
                                <?= number_format($mySubmission['grade'], 1) ?> / 10
                            </span>
                            <p class="text-muted mt-2 mb-0">
                                <small>Βαθμολογήθηκε στις <?= formatDate(
                                    $mySubmission['graded_at'],
                                ) ?></small>
                            </p>
                        <?php else: ?>
                            <p class="text-muted mb-0">Αναμένεται βαθμολόγηση...</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($isPast): ?>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Η προθεσμία έχει περάσει. Δεν μπορείς να υποβάλεις.
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Δεν έχεις κάνει υποβολή ακόμα.</p>
                            <a href="<?= url(
                                'dashboard/assignments/submit.php?id=' . $assignment['id'],
                            ) ?>"
                               class="btn btn-success">
                                <i class="bi bi-upload me-1"></i>Υποβολή Εργασίας
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isOwner): ?>
            <!-- Υποβολές (για καθηγητή) -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Υποβολές</h5>
                    <span class="badge bg-primary"><?= count($submissions) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($submissions)): ?>
                        <p class="text-muted mb-0">Δεν υπάρχουν υποβολές ακόμα.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Φοιτητής</th>
                                        <th>Ημερομηνία</th>
                                        <th>Αρχείο</th>
                                        <th>Βαθμός</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($sub['username']) ?></strong>
                                                <br><small class="text-muted"><?= e(
                                                    $sub['email'],
                                                ) ?></small>
                                            </td>
                                            <td><?= formatDate($sub['submitted_at']) ?></td>
                                            <td>
                                                <?php if ($sub['file_path']): ?>
                                                    <a href="<?= url(
                                                        'uploads/' .
                                                            basename((string) $sub['file_path']),
                                                    ) ?>" target="_blank">
                                                        <i class="bi bi-file-earmark me-1"></i>Αρχείο
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($sub['grade'] !== null): ?>
                                                    <span class="badge bg-<?= gradeColor(
                                                        $sub['grade'],
                                                    ) ?>">
                                                        <?= number_format($sub['grade'], 1) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= url(
                                                    'dashboard/grades/grade.php?id=' . $sub['id'],
                                                ) ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <?= $sub['grade'] !== null
                                                        ? 'Επεξεργασία'
                                                        : 'Βαθμολόγηση' ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Πληροφορίες</div>
            <div class="card-body">
                <p>
                    <i class="bi bi-book me-2 text-muted"></i>
                    <strong>Μάθημα:</strong><br>
                    <?= e($assignment['course_name']) ?>
                </p>
                <p>
                    <i class="bi bi-person me-2 text-muted"></i>
                    <strong>Καθηγητής:</strong><br>
                    <?= e($assignment['teacher_name']) ?>
                </p>
                <p>
                    <i class="bi bi-calendar me-2 text-muted"></i>
                    <strong>Δημιουργήθηκε:</strong><br>
                    <?= formatDate($assignment['created_at']) ?>
                </p>
                <?php if ($isOwner): ?>
                    <hr>
                    <a href="<?= url('dashboard/assignments/edit.php?id=' . $assignment['id']) ?>"
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-pencil me-1"></i>Επεξεργασία
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
