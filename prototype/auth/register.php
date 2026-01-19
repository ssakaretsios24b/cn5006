<?php
/**
 * Εγγραφή Χρήστη
 * Φόρμα εγγραφής με επιλογή ρόλου και κωδικό γραμματείας
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
    // Παίρνουμε τα δεδομένα
    $username = post('username');
    $email = post('email');
    $password = post('password');
    $password_confirm = post('password_confirm');
    $role = post('role'); // 'student' ή 'teacher'
    $registration_code = post('registration_code');

    // Κρατάμε τα παλιά για να τα εμφανίσουμε αν υπάρχει error
    $old = compact('username', 'email', 'role');

    // Validation
    if (empty($username)) {
        $errors[] = 'Το username είναι υποχρεωτικό';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Το username πρέπει να έχει τουλάχιστον 3 χαρακτήρες';
    }

    if (empty($email)) {
        $errors[] = 'Το email είναι υποχρεωτικό';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Το email δεν είναι έγκυρο';
    }

    if (empty($password)) {
        $errors[] = 'Ο κωδικός είναι υποχρεωτικός';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Οι κωδικοί δεν ταιριάζουν';
    }

    if (empty($role) || !in_array($role, ['student', 'teacher'])) {
        $errors[] = 'Επίλεξε έγκυρο ρόλο';
    }

    // Έλεγχος κωδικού εγγραφής
    $expectedCode = $role === 'student' ? STUDENT_CODE : TEACHER_CODE;
    if ($registration_code !== $expectedCode) {
        $errors[] = 'Ο κωδικός εγγραφής είναι λάθος';
    }

    // Αν δεν έχουμε errors, προχωράμε
    if (empty($errors)) {
        try {
            $db = getDB();

            // Έλεγχος αν υπάρχει ήδη το email
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Το email χρησιμοποιείται ήδη';
            } else {
                // Βρίσκουμε το role_id
                $role_id = $role === 'student' ? 1 : 2;

                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $db->prepare('
                    INSERT INTO users (username, email, password, role_id)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$username, $email, $hashedPassword, $role_id]);

                setFlash('success', 'Η εγγραφή ολοκληρώθηκε! Μπορείς τώρα να συνδεθείς.');
                redirect(url('auth/login.php'));
            }
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Εγγραφή - Archimedes University';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="auth-container">
        <div class="card auth-card">
            <div class="card-header">
                <h4><i class="bi bi-person-plus me-2"></i>Εγγραφή</h4>
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

                <form method="POST" class="needs-loading needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= e($old['username'] ?? '') ?>" required minlength="3">
                        <div class="form-text">Τουλάχιστον 3 χαρακτήρες</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($old['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Κωδικός</label>
                        <input type="password" class="form-control" id="password" name="password"
                               required minlength="6">
                        <div class="form-text">Τουλάχιστον 6 χαρακτήρες</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Επιβεβαίωση Κωδικού</label>
                        <input type="password" class="form-control" id="password_confirm"
                               name="password_confirm" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ρόλος</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="role_student"
                                   value="student" <?= ($old['role'] ?? 'student') === 'student'
                                       ? 'checked'
                                       : '' ?>>
                            <label class="form-check-label" for="role_student">
                                <i class="bi bi-mortarboard me-1"></i>Φοιτητής
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="role_teacher"
                                   value="teacher" <?= ($old['role'] ?? '') === 'teacher'
                                       ? 'checked'
                                       : '' ?>>
                            <label class="form-check-label" for="role_teacher">
                                <i class="bi bi-person-workspace me-1"></i>Καθηγητής
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="registration_code" class="form-label">
                            Κωδικός Εγγραφής
                            <small class="text-muted">(από τη Γραμματεία)</small>
                        </label>
                        <input type="text" class="form-control" id="registration_code"
                               name="registration_code" required>
                        <div class="form-text">
                            Ο κωδικός που σου έδωσε η Γραμματεία για την εγγραφή σου
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-person-plus me-1"></i>Εγγραφή
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center text-muted mb-0">
                    Έχεις ήδη λογαριασμό;
                    <a href="<?= url('auth/login.php') ?>">Συνδέσου</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
