<?php
/**
 * Λίστα Μαθημάτων
 * Καθηγητές βλέπουν τα δικά τους μαθήματα
 * Φοιτητές βλέπουν όλα τα διαθέσιμα μαθήματα
 */

require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Μαθήματα - Archimedes University';
$currentPage = 'courses';

require_once __DIR__ . '/../../includes/dashboard_header.php';

$db = getDB();
$userId = getUserId();

if (isTeacher()) {
    // Καθηγητής - δικά του μαθήματα
    $stmt = $db->prepare('
        SELECT c.*,
               (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
               (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
        FROM courses c
        WHERE c.teacher_id = ?
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$userId]);
    $courses = $stmt->fetchAll();
} else {
    // Φοιτητής - όλα τα μαθήματα με ένδειξη αν είναι εγγεγραμμένος
    $stmt = $db->prepare('
        SELECT c.*, u.username as teacher_name,
               (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
               (SELECT id FROM enrollments WHERE course_id = c.id AND student_id = ?) as is_enrolled
        FROM courses c
        JOIN users u ON c.teacher_id = u.id
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$userId]);
    $courses = $stmt->fetchAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-book me-2"></i>Μαθήματα</h2>
    <?php if (isTeacher()): ?>
        <a href="<?= url('dashboard/courses/create.php') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Νέο Μάθημα
        </a>
    <?php endif; ?>
</div>

<?php if (empty($courses)): ?>
    <div class="alert alert-info">
        <?php if (isTeacher()): ?>
            Δεν έχεις δημιουργήσει μαθήματα ακόμα.
            <a href="<?= url('dashboard/courses/create.php') ?>">Δημιούργησε το πρώτο σου μάθημα</a>
        <?php else: ?>
            Δεν υπάρχουν διαθέσιμα μαθήματα αυτή τη στιγμή.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($courses as $course): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?= e($course['name']) ?>
                        </h5>
                        <?php if (isStudent()): ?>
                            <p class="card-subtitle text-muted small mb-2">
                                <i class="bi bi-person me-1"></i><?= e($course['teacher_name']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="card-text text-muted">
                            <?= e(substr($course['description'] ?? 'Χωρίς περιγραφή', 0, 100)) ?>
                            <?= strlen($course['description'] ?? '') > 100 ? '...' : '' ?>
                        </p>
                        <div class="d-flex gap-3 text-muted small mb-3">
                            <span><i class="bi bi-people me-1"></i><?= $course[
                                'student_count'
                            ] ?> φοιτητές</span>
                            <?php if (isTeacher()): ?>
                                <span><i class="bi bi-clipboard me-1"></i><?= $course[
                                    'assignment_count'
                                ] ?> εργασίες</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <?php if (isTeacher()): ?>
                            <a href="<?= url(
                                'dashboard/courses/view.php?id=' . $course['id'],
                            ) ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i>Προβολή
                            </a>
                            <a href="<?= url(
                                'dashboard/courses/edit.php?id=' . $course['id'],
                            ) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil me-1"></i>Επεξεργασία
                            </a>
                        <?php else: ?>
                            <?php if ($course['is_enrolled']): ?>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <span class="badge bg-success d-flex align-items-center" style="height: 31px;">
                                        <i class="bi bi-check-circle me-1"></i>Εγγεγραμμένος
                                    </span>
                                    <a href="<?= url(
                                        'dashboard/courses/view.php?id=' . $course['id'],
                                    ) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i>Προβολή
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="<?= url(
                                    'dashboard/courses/enroll.php?id=' . $course['id'],
                                ) ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-plus-circle me-1"></i>Εγγραφή
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
