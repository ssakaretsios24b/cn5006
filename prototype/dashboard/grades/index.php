<?php
/**
 * Βαθμολογίες
 * Φοιτητές: βλέπουν τους βαθμούς τους
 * Καθηγητές: βλέπουν υποβολές προς βαθμολόγηση
 */

require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Βαθμολογίες - Archimedes University';
$currentPage = 'grades';

require_once __DIR__ . '/../../includes/dashboard_header.php';

$db = getDB();
$userId = getUserId();

if (isStudent()) {
    // Όλοι οι βαθμοί του φοιτητή
    $stmt = $db->prepare('
        SELECT s.*, a.title as assignment_title, c.name as course_name
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE s.student_id = ?
        ORDER BY s.graded_at DESC, s.submitted_at DESC
    ');
    $stmt->execute([$userId]);
    $submissions = $stmt->fetchAll();

    // Στατιστικά
    $graded = array_filter($submissions, fn($s) => $s['grade'] !== null);
    $avgGrade =
        count($graded) > 0 ? array_sum(array_column($graded, 'grade')) / count($graded) : null;
} else {
    // Υποβολές προς βαθμολόγηση για τον καθηγητή
    $stmt = $db->prepare('
        SELECT s.*, u.username, u.email, a.title as assignment_title, c.name as course_name
        FROM submissions s
        JOIN users u ON s.student_id = u.id
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE c.teacher_id = ?
        ORDER BY
            CASE WHEN s.grade IS NULL THEN 0 ELSE 1 END,
            s.submitted_at DESC
    ');
    $stmt->execute([$userId]);
    $submissions = $stmt->fetchAll();

    $pendingCount = count(array_filter($submissions, fn($s) => $s['grade'] === null));
}
?>

<h2 class="mb-4">
    <i class="bi bi-star me-2"></i>
    <?= isStudent() ? 'Οι Βαθμοί μου' : 'Βαθμολόγηση' ?>
</h2>

<?php if (isStudent()): ?>
    <!-- Στατιστικά φοιτητή -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number"><?= count($submissions) ?></div>
                <div class="stat-label">Υποβολές</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card gold">
                <div class="stat-number"><?= count($graded) ?></div>
                <div class="stat-label">Βαθμολογημένες</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card success">
                <div class="stat-number"><?= $avgGrade ? number_format($avgGrade, 1) : '-' ?></div>
                <div class="stat-label">Μέσος Όρος</div>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php if ($pendingCount > 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Έχεις <strong><?= $pendingCount ?></strong> υποβολές που περιμένουν βαθμολόγηση.
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (empty($submissions)): ?>
    <div class="alert alert-info">
        <?php if (isStudent()): ?>
            Δεν έχεις κάνει υποβολές ακόμα.
        <?php else: ?>
            Δεν υπάρχουν υποβολές από τους φοιτητές σου.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <?php if (isTeacher()): ?>
                            <th>Φοιτητής</th>
                        <?php endif; ?>
                        <th>Εργασία</th>
                        <th>Μάθημα</th>
                        <th>Υποβλήθηκε</th>
                        <th>Βαθμός</th>
                        <?php if (isTeacher()): ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                        <tr class="<?= $sub['grade'] === null ? 'table-warning' : '' ?>">
                            <?php if (isTeacher()): ?>
                                <td>
                                    <strong><?= e($sub['username']) ?></strong>
                                </td>
                            <?php endif; ?>
                            <td><?= e($sub['assignment_title']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= e($sub['course_name']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($sub['submitted_at']) ?></td>
                            <td>
                                <?php if ($sub['grade'] !== null): ?>
                                    <span class="badge bg-<?= gradeColor($sub['grade']) ?> fs-6">
                                        <?= number_format($sub['grade'], 1) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Αναμένεται</span>
                                <?php endif; ?>
                            </td>
                            <?php if (isTeacher()): ?>
                                <td>
                                    <a href="<?= url(
                                        'dashboard/grades/grade.php?id=' . $sub['id'],
                                    ) ?>"
                                       class="btn btn-sm <?= $sub['grade'] === null
                                           ? 'btn-success'
                                           : 'btn-outline-primary' ?>">
                                        <?= $sub['grade'] === null
                                            ? 'Βαθμολόγηση'
                                            : 'Επεξεργασία' ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
