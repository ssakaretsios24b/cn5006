<?php
/**
 * Εγγραφή σε Μάθημα
 * Μόνο για φοιτητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireStudent();

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

// Έλεγχος αν είναι ήδη εγγεγραμμένος
$stmt = $db->prepare('SELECT id FROM enrollments WHERE course_id = ? AND student_id = ?');
$stmt->execute([$courseId, $userId]);
if ($stmt->fetch()) {
    setFlash('info', 'Είσαι ήδη εγγεγραμμένος σε αυτό το μάθημα.');
    redirect(url('dashboard/courses/view.php?id=' . $courseId));
}

// Αν είναι POST, κάνουμε την εγγραφή
if (isPost()) {
    try {
        $stmt = $db->prepare('INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)');
        $stmt->execute([$courseId, $userId]);

        setFlash('success', 'Εγγράφηκες επιτυχώς στο μάθημα!');
        redirect(url('dashboard/courses/view.php?id=' . $courseId));
    } catch (PDOException $e) {
        setFlash('error', 'Σφάλμα εγγραφής: ' . $e->getMessage());
        redirect(url('dashboard/courses/'));
    }
}

$pageTitle = 'Εγγραφή σε Μάθημα - Archimedes University';
$currentPage = 'courses';

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/courses/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στα Μαθήματα
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Εγγραφή σε Μάθημα</h5>
            </div>
            <div class="card-body text-center py-4">
                <i class="bi bi-book text-primary" style="font-size: 4rem;"></i>
                <h3 class="mt-3"><?= e($course['name']) ?></h3>
                <p class="text-muted">
                    <i class="bi bi-person me-1"></i>Καθηγητής: <?= e($course['teacher_name']) ?>
                </p>
                <?php if ($course['description']): ?>
                    <p class="mt-3"><?= e($course['description']) ?></p>
                <?php endif; ?>

                <hr class="my-4">

                <p>Θέλεις να εγγραφείς σε αυτό το μάθημα;</p>

                <form method="POST">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-1"></i>Επιβεβαίωση Εγγραφής
                    </button>
                    <a href="<?= url(
                        'dashboard/courses/',
                    ) ?>" class="btn btn-outline-secondary btn-lg">
                        Ακύρωση
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
