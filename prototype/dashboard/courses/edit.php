<?php
/**
 * Επεξεργασία Μαθήματος
 * Μόνο ο καθηγητής που το δημιούργησε
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireTeacher();

$db = getDB();
$courseId = (int) get('id');
$userId = getUserId();

// Βρίσκουμε το μάθημα και ελέγχουμε ότι ανήκει σε αυτόν τον καθηγητή
$stmt = $db->prepare('SELECT * FROM courses WHERE id = ? AND teacher_id = ?');
$stmt->execute([$courseId, $userId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Το μάθημα δεν βρέθηκε ή δεν έχεις δικαίωμα επεξεργασίας.');
    redirect(url('dashboard/courses/'));
}

$pageTitle = 'Επεξεργασία: ' . $course['name'];
$currentPage = 'courses';

$errors = [];
$old = [
    'name' => $course['name'],
    'description' => $course['description'],
];

if (isPost()) {
    $name = post('name');
    $description = post('description');
    $action = post('action');

    $old = compact('name', 'description');

    // Διαγραφή μαθήματος
    if ($action === 'delete') {
        try {
            $stmt = $db->prepare('DELETE FROM courses WHERE id = ? AND teacher_id = ?');
            $stmt->execute([$courseId, $userId]);

            setFlash('success', 'Το μάθημα διαγράφηκε.');
            redirect(url('dashboard/courses/'));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα διαγραφής: ' . $e->getMessage();
        }
    } else {
        // Update μαθήματος
        if (empty($name)) {
            $errors[] = 'Το όνομα είναι υποχρεωτικό';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare('
                    UPDATE courses SET name = ?, description = ?
                    WHERE id = ? AND teacher_id = ?
                ');
                $stmt->execute([$name, $description, $courseId, $userId]);

                setFlash('success', 'Το μάθημα ενημερώθηκε!');
                redirect(url('dashboard/courses/'));
            } catch (PDOException $e) {
                $errors[] = 'Σφάλμα ενημέρωσης: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/courses/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στα Μαθήματα
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Επεξεργασία Μαθήματος</h5>
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
                        <label for="name" class="form-label">Όνομα Μαθήματος *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($old['name']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="4"><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Αποθήκευση
                    </button>
                    <a href="<?= url(
                        'dashboard/courses/',
                    ) ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                </form>
            </div>
        </div>

        <!-- Διαγραφή -->
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>Επικίνδυνη Ζώνη
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Η διαγραφή του μαθήματος θα διαγράψει επίσης όλες τις εργασίες και υποβολές.
                    Αυτή η ενέργεια δεν αναιρείται.
                </p>
                <form method="POST" onsubmit="return confirm('Είσαι σίγουρος; Όλα τα δεδομένα θα χαθούν!');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Διαγραφή Μαθήματος
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
