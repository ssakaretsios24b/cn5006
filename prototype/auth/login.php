<?php
/**
 * Σύνδεση Χρήστη
 * Φόρμα login με email και password
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

// Αν είναι ήδη συνδεδεμένος, πήγαινε στο dashboard
requireGuest();

$errors = [];
$old = [];

// Χειρισμός POST request
if (isPost()) {
    $email = post('email');
    $password = post('password');

    $old = compact('email');

    // Validation
    if (empty($email)) {
        $errors[] = 'Το email είναι υποχρεωτικό';
    }

    if (empty($password)) {
        $errors[] = 'Ο κωδικός είναι υποχρεωτικός';
    }

    if (empty($errors)) {
        try {
            $db = getDB();

            // Βρίσκουμε τον χρήστη με JOIN στον πίνακα roles
            $stmt = $db->prepare('
                SELECT u.*, r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.email = ?
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Έλεγχος password
            if ($user && password_verify($password, $user['password'])) {
                // Επιτυχής σύνδεση
                loginUser($user);
                setFlash('success', 'Καλωσήρθες, ' . $user['username'] . '!');
                redirect(url('dashboard/'));
            } else {
                $errors[] = 'Λάθος email ή κωδικός';
            }
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Σύνδεση - Archimedes University';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header">
                <h4><i class="bi bi-box-arrow-in-right me-2"></i>Σύνδεση</h4>
            </div>
            <div class="card-body p-4">
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
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($old['email'] ?? '') ?>" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Κωδικός</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Σύνδεση
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center text-muted mb-0">
                    Δεν έχεις λογαριασμό;
                    <a href="<?= url('auth/register.php') ?>">Εγγράψου</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
