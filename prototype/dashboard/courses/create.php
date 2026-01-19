<?php
/**
 * Δημιουργία Μαθήματος
 * Μόνο για καθηγητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

// Μόνο καθηγητές
requireTeacher();

$pageTitle = 'Νέο Μάθημα - Archimedes University';
$currentPage = 'courses';

$errors = [];
$old = [];

if (isPost()) {
    $name = post('name');
    $description = post('description');

    $old = compact('name', 'description');

    // Validation
    if (empty($name)) {
        $errors[] = 'Το όνομα είναι υποχρεωτικό';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Το όνομα πρέπει να έχει τουλάχιστον 3 χαρακτήρες';
    }

    if (empty($errors)) {
        try {
            $db = getDB();

            $stmt = $db->prepare('
                INSERT INTO courses (teacher_id, name, description)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([getUserId(), $name, $description]);

            setFlash('success', 'Το μάθημα δημιουργήθηκε επιτυχώς!');
            redirect(url('dashboard/courses/'));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης: ' . $e->getMessage();
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
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Δημιουργία Μαθήματος</h5>
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
                               value="<?= e($old['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="4"><?= e($old['description'] ?? '') ?></textarea>
                        <div class="form-text">Προαιρετική περιγραφή του μαθήματος</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Δημιουργία
                    </button>
                    <a href="<?= url(
                        'dashboard/courses/',
                    ) ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
