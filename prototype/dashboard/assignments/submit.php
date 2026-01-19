<?php
/**
 * Υποβολή Εργασίας
 * Μόνο για φοιτητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireStudent();

$db = getDB();
$assignmentId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε την εργασία
$stmt = $db->prepare('
    SELECT a.*, c.name as course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ?
');
$stmt->execute([$assignmentId]);
$assignment = $stmt->fetch();

if (!$assignment) {
    setFlash('error', 'Η εργασία δεν βρέθηκε.');
    redirect(url('dashboard/assignments/'));
}

// Έλεγχος εγγραφής στο μάθημα
$stmt = $db->prepare('SELECT id FROM enrollments WHERE course_id = ? AND student_id = ?');
$stmt->execute([$assignment['course_id'], $userId]);
if (!$stmt->fetch()) {
    showForbidden();
}

// Έλεγχος αν έχει περάσει η προθεσμία
if (isPastDue($assignment['due_date'])) {
    setFlash('error', 'Η προθεσμία έχει περάσει.');
    redirect(url('dashboard/assignments/view.php?id=' . $assignmentId));
}

// Έλεγχος αν έχει ήδη κάνει υποβολή
$stmt = $db->prepare('SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?');
$stmt->execute([$assignmentId, $userId]);
$existingSubmission = $stmt->fetch();

$pageTitle = 'Υποβολή: ' . $assignment['title'];
$currentPage = 'assignments';

$errors = [];

if (isPost()) {
    $comments = post('comments');
    $uploadedFile = null;

    // Χειρισμός αρχείου
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Σφάλμα στο upload του αρχείου';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            // 10MB limit
            $errors[] = 'Το αρχείο είναι πολύ μεγάλο (max 10MB)';
        } else {
            // Επιτρέπουμε μόνο συγκεκριμένους τύπους αρχείων
            $allowedExts = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]+/', '', $ext);
            if ($ext === '' || !in_array($ext, $allowedExts, true)) {
                $errors[] = 'Μη επιτρεπτός τύπος αρχείου';
            } else {
                // Best-effort MIME check
                $allowedMimes = [
                    'pdf' => ['application/pdf'],
                    'doc' => ['application/msword', 'application/octet-stream'],
                    'docx' => [
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/zip',
                        'application/octet-stream',
                    ],
                    'txt' => ['text/plain', 'application/octet-stream'],
                    'zip' => [
                        'application/zip',
                        'application/x-zip-compressed',
                        'application/octet-stream',
                    ],
                    'rar' => [
                        'application/vnd.rar',
                        'application/x-rar',
                        'application/octet-stream',
                    ],
                    'jpg' => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'],
                    'png' => ['image/png'],
                    'gif' => ['image/gif'],
                ];

                if (class_exists('finfo')) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = (string) $finfo->file($file['tmp_name']);
                    $allowed = $allowedMimes[$ext] ?? [];
                    if ($mime !== '' && !in_array($mime, $allowed, true)) {
                        $errors[] = 'Ο τύπος του αρχείου δεν είναι αποδεκτός';
                    }
                }
            }

            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (empty($errors)) {
                // Δημιουργούμε μοναδικό όνομα (χωρίς user-provided filename)
                $filename = sprintf(
                    '%d_%d_%s_%s.%s',
                    $userId,
                    $assignmentId,
                    date('Ymd_His'),
                    bin2hex(random_bytes(6)),
                    $ext,
                );

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $uploadedFile = $filename;
                } else {
                    $errors[] = 'Αποτυχία αποθήκευσης αρχείου';
                }
            }
        }
    }

    // Πρέπει να έχει είτε αρχείο είτε σχόλια
    if (!$uploadedFile && empty($comments) && !$existingSubmission) {
        $errors[] = 'Πρέπει να ανεβάσεις αρχείο ή να γράψεις σχόλια';
    }

    if (empty($errors)) {
        try {
            if ($existingSubmission) {
                // Update υπάρχουσας υποβολής
                $sql = 'UPDATE submissions SET comments = ?, submitted_at = NOW()';
                $params = [$comments];

                if ($uploadedFile) {
                    $sql .= ', file_path = ?';
                    $params[] = $uploadedFile;
                    // Διαγραφή παλιού αρχείου
                    if ($existingSubmission['file_path']) {
                        $oldFile = basename((string) $existingSubmission['file_path']);
                        @unlink(__DIR__ . '/../../uploads/' . $oldFile);
                    }
                }

                $sql .= ' WHERE id = ?';
                $params[] = $existingSubmission['id'];

                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                setFlash('success', 'Η υποβολή ενημερώθηκε!');
            } else {
                // Νέα υποβολή
                $stmt = $db->prepare('
                    INSERT INTO submissions (assignment_id, student_id, file_path, comments)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$assignmentId, $userId, $uploadedFile, $comments]);

                setFlash('success', 'Η υποβολή ολοκληρώθηκε επιτυχώς!');
            }

            redirect(url('dashboard/assignments/view.php?id=' . $assignmentId));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url(
        'dashboard/assignments/view.php?id=' . $assignmentId,
    ) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στην Εργασία
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-upload me-2"></i>
                    <?= $existingSubmission ? 'Επεξεργασία Υποβολής' : 'Υποβολή Εργασίας' ?>
                </h5>
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

                <?php if ($existingSubmission): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Έχεις ήδη κάνει υποβολή. Μπορείς να την ενημερώσεις.
                        <?php if ($existingSubmission['file_path']): ?>
                            <br>Τρέχον αρχείο:
                            <a href="<?= url(
                                'uploads/' . basename((string) $existingSubmission['file_path']),
                            ) ?>" target="_blank">
                                <?= e(basename($existingSubmission['file_path'])) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-light p-3 rounded mb-4">
                    <h6><?= e($assignment['title']) ?></h6>
                    <p class="text-muted mb-0">
                        Μάθημα: <?= e($assignment['course_name']) ?>
                        <?php if ($assignment['due_date']): ?>
                            | Προθεσμία: <?= formatDate($assignment['due_date']) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="needs-loading">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="file" class="form-label">
                            Αρχείο <?= $existingSubmission ? '(προαιρετικά για ενημέρωση)' : '' ?>
                        </label>
                        <input type="file" class="form-control" id="file" name="file">
                        <div class="form-text">Μέγιστο μέγεθος: 10MB</div>
                    </div>

                    <div class="mb-4">
                        <label for="comments" class="form-label">Σχόλια (προαιρετικά)</label>
                        <textarea class="form-control" id="comments" name="comments" rows="4"><?= e(
                            $existingSubmission['comments'] ?? '',
                        ) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>
                        <?= $existingSubmission ? 'Ενημέρωση' : 'Υποβολή' ?>
                    </button>
                    <a href="<?= url(
                        'dashboard/assignments/view.php?id=' . $assignmentId,
                    ) ?>" class="btn btn-outline-secondary">
                        Ακύρωση
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
