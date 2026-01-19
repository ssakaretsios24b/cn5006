<?php
/**
 * Dashboard - Κεντρική Σελίδα
 * Δείχνει διαφορετικά στατιστικά ανάλογα με τον ρόλο
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard - Archimedes University';
$currentPage = 'dashboard';

require_once __DIR__ . '/../includes/dashboard_header.php';

$db = getDB();
$userId = getUserId();

// Στατιστικά ανάλογα με τον ρόλο
if (isTeacher()) {
    // Πόσα μαθήματα έχει δημιουργήσει
    $stmt = $db->prepare('SELECT COUNT(*) FROM courses WHERE teacher_id = ?');
    $stmt->execute([$userId]);
    $totalCourses = $stmt->fetchColumn();

    // Πόσοι φοιτητές είναι εγγεγραμμένοι στα μαθήματά του
    $stmt = $db->prepare('
        SELECT COUNT(DISTINCT e.student_id)
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE c.teacher_id = ?
    ');
    $stmt->execute([$userId]);
    $totalStudents = $stmt->fetchColumn();

    // Πόσες υποβολές περιμένουν βαθμολόγηση
    $stmt = $db->prepare('
        SELECT COUNT(*)
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE c.teacher_id = ? AND s.grade IS NULL
    ');
    $stmt->execute([$userId]);
    $pendingGrades = $stmt->fetchColumn();

    // Πρόσφατες υποβολές
    $stmt = $db->prepare('
        SELECT s.*, u.username, a.title as assignment_title, c.name as course_name
        FROM submissions s
        JOIN users u ON s.student_id = u.id
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE c.teacher_id = ?
        ORDER BY s.submitted_at DESC
        LIMIT 5
    ');
    $stmt->execute([$userId]);
    $recentSubmissions = $stmt->fetchAll();
} else {
    // Φοιτητής

    // Πόσα μαθήματα έχει εγγραφεί
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = ?');
    $stmt->execute([$userId]);
    $enrolledCourses = $stmt->fetchColumn();

    // Πόσες εργασίες έχει να κάνει (εκκρεμείς)
    $stmt = $db->prepare('
        SELECT COUNT(*)
        FROM assignments a
        JOIN enrollments e ON a.course_id = e.course_id
        LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
        WHERE e.student_id = ? AND s.id IS NULL AND (a.due_date IS NULL OR a.due_date > NOW())
    ');
    $stmt->execute([$userId, $userId]);
    $pendingAssignments = $stmt->fetchColumn();

    // Μέσος όρος βαθμών
    $stmt = $db->prepare('
        SELECT AVG(s.grade)
        FROM submissions s
        WHERE s.student_id = ? AND s.grade IS NOT NULL
    ');
    $stmt->execute([$userId]);
    $avgGrade = $stmt->fetchColumn();

    // Πρόσφατοι βαθμοί
    $stmt = $db->prepare('
        SELECT s.grade, s.graded_at, a.title as assignment_title, c.name as course_name
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE s.student_id = ? AND s.grade IS NOT NULL
        ORDER BY s.graded_at DESC
        LIMIT 5
    ');
    $stmt->execute([$userId]);
    $recentGrades = $stmt->fetchAll();
}
?>

<h2 class="mb-4">
    Καλωσήρθες, <?= e(getUsername()) ?>!
    <small class="text-muted fs-6">
        <?= isTeacher() ? 'Καθηγητής' : 'Φοιτητής' ?>
    </small>
</h2>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php if (isTeacher()): ?>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number"><?= $totalCourses ?></div>
                <div class="stat-label">Μαθήματα</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card gold">
                <div class="stat-number"><?= $totalStudents ?></div>
                <div class="stat-label">Εγγεγραμμένοι Φοιτητές</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card <?= $pendingGrades > 0 ? 'success' : '' ?>">
                <div class="stat-number"><?= $pendingGrades ?></div>
                <div class="stat-label">Προς Βαθμολόγηση</div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-number"><?= $enrolledCourses ?></div>
                <div class="stat-label">Μαθήματα</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card gold">
                <div class="stat-number"><?= $pendingAssignments ?></div>
                <div class="stat-label">Εκκρεμείς Εργασίες</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card success">
                <div class="stat-number"><?= $avgGrade ? number_format($avgGrade, 1) : '-' ?></div>
                <div class="stat-label">Μέσος Όρος</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Γρήγορες Ενέργειες
            </div>
            <div class="card-body">
                <?php if (isTeacher()): ?>
                    <a href="<?= url(
                        'dashboard/courses/create.php',
                    ) ?>" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle me-1"></i>Νέο Μάθημα
                    </a>
                    <a href="<?= url(
                        'dashboard/assignments/create.php',
                    ) ?>" class="btn btn-secondary me-2">
                        <i class="bi bi-clipboard-plus me-1"></i>Νέα Εργασία
                    </a>
                    <?php if ($pendingGrades > 0): ?>
                    <a href="<?= url('dashboard/grades/') ?>" class="btn btn-success">
                        <i class="bi bi-check2-square me-1"></i>Βαθμολόγηση (<?= $pendingGrades ?>)
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= url('dashboard/courses/') ?>" class="btn btn-primary me-2">
                        <i class="bi bi-book me-1"></i>Δες Μαθήματα
                    </a>
                    <?php if ($pendingAssignments > 0): ?>
                    <a href="<?= url('dashboard/assignments/') ?>" class="btn btn-secondary">
                        <i class="bi bi-clipboard-check me-1"></i>Εκκρεμείς Εργασίες (<?= $pendingAssignments ?>)
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Πρόσφατη Δραστηριότητα
            </div>
            <div class="card-body">
                <?php if (isTeacher()): ?>
                    <?php if (empty($recentSubmissions)): ?>
                        <p class="text-muted mb-0">Δεν υπάρχουν πρόσφατες υποβολές.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Φοιτητής</th>
                                        <th>Εργασία</th>
                                        <th>Μάθημα</th>
                                        <th>Ημερομηνία</th>
                                        <th>Κατάσταση</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSubmissions as $sub): ?>
                                    <tr>
                                        <td><?= e($sub['username']) ?></td>
                                        <td><?= e($sub['assignment_title']) ?></td>
                                        <td><?= e($sub['course_name']) ?></td>
                                        <td><?= formatDate($sub['submitted_at']) ?></td>
                                        <td>
                                            <?php if ($sub['grade'] !== null): ?>
                                                <span class="badge bg-<?= gradeColor(
                                                    $sub['grade'],
                                                ) ?>">
                                                    <?= number_format($sub['grade'], 1) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Αβαθμολόγητη</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (empty($recentGrades)): ?>
                        <p class="text-muted mb-0">Δεν έχεις λάβει βαθμούς ακόμα.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Εργασία</th>
                                        <th>Μάθημα</th>
                                        <th>Βαθμός</th>
                                        <th>Ημερομηνία</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentGrades as $grade): ?>
                                    <tr>
                                        <td><?= e($grade['assignment_title']) ?></td>
                                        <td><?= e($grade['course_name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= gradeColor(
                                                $grade['grade'],
                                            ) ?> fs-6">
                                                <?= number_format($grade['grade'], 1) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($grade['graded_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
