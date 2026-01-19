<?php
/**
 * Προβολή Μαθήματος
 * Δείχνει εργασίες και εγγεγραμμένους φοιτητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireLogin();

$db = getDB();
$courseId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε το μάθημα
$stmt = $db->prepare('
    SELECT c.*, u.username as teacher_name
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ?
');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Το μάθημα δεν βρέθηκε.');
    redirect(url('dashboard/courses/'));
}

// Έλεγχος πρόσβασης
$isOwner = isTeacher() && $course['teacher_id'] == $userId;

if (isStudent()) {
    // Έλεγχος αν είναι εγγεγραμμένος
    $stmt = $db->prepare('SELECT id FROM enrollments WHERE course_id = ? AND student_id = ?');
    $stmt->execute([$courseId, $userId]);
    $isEnrolled = $stmt->fetch();

    if (!$isEnrolled) {
        setFlash('error', 'Δεν είσαι εγγεγραμμένος σε αυτό το μάθημα.');
        redirect(url('dashboard/courses/'));
    }
}

// Εργασίες του μαθήματος
$stmt = $db->prepare('
    SELECT a.*,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
    FROM assignments a
    WHERE a.course_id = ?
    ORDER BY a.due_date ASC, a.created_at DESC
');
$stmt->execute([$courseId]);
$assignments = $stmt->fetchAll();

// Φοιτητές (μόνο για καθηγητή)
$students = [];
if (isTeacher() && $isOwner) {
    $stmt = $db->prepare('
        SELECT u.id, u.username, u.email, e.enrolled_at
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.course_id = ?
        ORDER BY e.enrolled_at DESC
    ');
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll();
}

$pageTitle = $course['name'] . ' - Archimedes University';
$currentPage = 'courses';

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/courses/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στα Μαθήματα
    </a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2><?= e($course['name']) ?></h2>
        <p class="text-muted mb-0">
            <i class="bi bi-person me-1"></i>Καθηγητής: <?= e($course['teacher_name']) ?>
        </p>
    </div>
    <?php if ($isOwner): ?>
        <div>
            <a href="<?= url(
                'dashboard/courses/edit.php?id=' . $course['id'],
            ) ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Επεξεργασία
            </a>
            <a href="<?= url(
                'dashboard/assignments/create.php?course_id=' . $course['id'],
            ) ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Νέα Εργασία
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($course['description']): ?>
<div class="card mb-4">
    <div class="card-body">
        <p class="mb-0"><?= nl2br(e($course['description'])) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Εργασίες -->
    <div class="<?= $isOwner ? 'col-lg-8' : 'col-12' ?>">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clipboard me-2"></i>Εργασίες</h5>
                <span class="badge bg-primary"><?= count($assignments) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted mb-0">Δεν υπάρχουν εργασίες ακόμα.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Τίτλος</th>
                                    <th>Προθεσμία</th>
                                    <?php if ($isOwner): ?>
                                        <th>Υποβολές</th>
                                    <?php endif; ?>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <?php
                                    $isPast = isPastDue($assignment['due_date']);
                                    $daysLeft = daysUntilDue($assignment['due_date']);
                                    ?>
                                    <tr>
                                        <td><?= e($assignment['title']) ?></td>
                                        <td>
                                            <?php if ($assignment['due_date']): ?>
                                                <span class="<?= $isPast ? 'text-danger' : '' ?>">
                                                    <?= formatDate(
                                                        $assignment['due_date'],
                                                        'd/m/Y',
                                                    ) ?>
                                                </span>
                                                <?php if (
                                                    !$isPast &&
                                                    $daysLeft !== null &&
                                                    $daysLeft <= 3
                                                ): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <?= $daysLeft ?> μέρες
                                                    </span>
                                                <?php elseif ($isPast): ?>
                                                    <span class="badge bg-danger">Έληξε</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isOwner): ?>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= $assignment['submission_count'] ?>
                                                </span>
                                            </td>
                                        <?php endif; ?>
                                        <td class="text-end">
                                            <a href="<?= url(
                                                'dashboard/assignments/view.php?id=' .
                                                    $assignment['id'],
                                            ) ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                Προβολή
                                            </a>
                                            <?php if (isStudent()): ?>
                                                <a href="<?= url(
                                                    'dashboard/assignments/submit.php?id=' .
                                                        $assignment['id'],
                                                ) ?>"
                                                   class="btn btn-sm btn-success">
                                                    Υποβολή
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Φοιτητές (μόνο για καθηγητή) -->
    <?php if ($isOwner): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Φοιτητές</h5>
                <span class="badge bg-primary"><?= count($students) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <p class="text-muted mb-0">Κανένας φοιτητής δεν έχει εγγραφεί ακόμα.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($students as $student): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?= e($student['username']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= e($student['email']) ?></small>
                                </div>
                                <small class="text-muted">
                                    <?= formatDate($student['enrolled_at'], 'd/m') ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
