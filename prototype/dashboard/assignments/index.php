<?php
/**
 * Λίστα Εργασιών
 * Καθηγητές: όλες τις εργασίες των μαθημάτων τους
 * Φοιτητές: εργασίες από μαθήματα που είναι εγγεγραμμένοι
 */

require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Εργασίες - Archimedes University';
$currentPage = 'assignments';

require_once __DIR__ . '/../../includes/dashboard_header.php';

$db = getDB();
$userId = getUserId();

if (isTeacher()) {
    // Όλες οι εργασίες των μαθημάτων του καθηγητή
    $stmt = $db->prepare('
        SELECT a.*, c.name as course_name,
               (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count,
               (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id AND grade IS NULL) as pending_count
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        WHERE c.teacher_id = ?
        ORDER BY a.due_date ASC, a.created_at DESC
    ');
    $stmt->execute([$userId]);
} else {
    // Εργασίες από μαθήματα που είναι εγγεγραμμένος ο φοιτητής
    $stmt = $db->prepare('
        SELECT a.*, c.name as course_name,
               s.id as submission_id, s.grade, s.submitted_at
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
        WHERE e.student_id = ?
        ORDER BY a.due_date ASC, a.created_at DESC
    ');
    $stmt->execute([$userId, $userId]);
}

$assignments = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check me-2"></i>Εργασίες</h2>
    <?php if (isTeacher()): ?>
        <a href="<?= url('dashboard/assignments/create.php') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Νέα Εργασία
        </a>
    <?php endif; ?>
</div>

<?php if (empty($assignments)): ?>
    <div class="alert alert-info">
        <?php if (isTeacher()): ?>
            Δεν έχεις δημιουργήσει εργασίες ακόμα.
        <?php else: ?>
            Δεν υπάρχουν εργασίες στα μαθήματά σου.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Τίτλος</th>
                        <th>Μάθημα</th>
                        <th>Προθεσμία</th>
                        <?php if (isTeacher()): ?>
                            <th>Υποβολές</th>
                        <?php else: ?>
                            <th>Κατάσταση</th>
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
                            <td>
                                <strong><?= e($assignment['title']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= e($assignment['course_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($assignment['due_date']): ?>
                                    <span class="<?= $isPast ? 'text-danger' : '' ?>">
                                        <?= formatDate($assignment['due_date'], 'd/m/Y H:i') ?>
                                    </span>
                                    <?php if ($isPast): ?>
                                        <span class="badge bg-danger">Έληξε</span>
                                    <?php elseif ($daysLeft !== null && $daysLeft <= 3): ?>
                                        <span class="badge bg-warning text-dark"><?= $daysLeft ?> μέρες</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Χωρίς προθεσμία</span>
                                <?php endif; ?>
                            </td>
                            <?php if (isTeacher()): ?>
                                <td>
                                    <span class="badge bg-primary"><?= $assignment[
                                        'submission_count'
                                    ] ?></span>
                                    <?php if ($assignment['pending_count'] > 0): ?>
                                        <span class="badge bg-warning text-dark">
                                            <?= $assignment['pending_count'] ?> αβαθμολόγητες
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <td>
                                    <?php if ($assignment['submission_id']): ?>
                                        <?php if ($assignment['grade'] !== null): ?>
                                            <span class="badge bg-<?= gradeColor(
                                                $assignment['grade'],
                                            ) ?>">
                                                Βαθμός: <?= number_format(
                                                    $assignment['grade'],
                                                    1,
                                                ) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Υποβλήθηκε</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Εκκρεμεί</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-end">
                                <a href="<?= url(
                                    'dashboard/assignments/view.php?id=' . $assignment['id'],
                                ) ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    Προβολή
                                </a>
                                <?php if (
                                    isStudent() &&
                                    !$assignment['submission_id'] &&
                                    !$isPast
                                ): ?>
                                    <a href="<?= url(
                                        'dashboard/assignments/submit.php?id=' . $assignment['id'],
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
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
