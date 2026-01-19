<?php
/**
 * Auth Functions
 * Διαχείριση sessions και authentication
 */

// Ξεκινάμε το session αν δεν έχει ξεκινήσει
if (session_status() === PHP_SESSION_NONE) {
    // Session cookie parameters
    // - HttpOnly: μειώνει τον κίνδυνο να διαρρεύσει το session id μέσω XSS (η js δεν μπορεί να διαβάσει το cookie).
    // - Secure: ενεργοποιείται για HTTPS
    // - SameSite=Lax: μειώνει τον κίνδυνο CSRF επιθέσεων
    session_set_cookie_params([
        'lifetime' => 3600,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// Κωδικοί εγγραφής από τη γραμματεία
define('STUDENT_CODE', 'STUD2025');
define('TEACHER_CODE', 'PROF2025');

/**
 * Ελέγχει αν ο χρήστης είναι συνδεδεμένος
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Επιστρέφει το user_id αν υπάρχει
 */
function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Επιστρέφει το username
 */
function getUsername(): ?string {
    return $_SESSION['username'] ?? null;
}

/**
 * Επιστρέφει το role_id (1=student, 2=teacher)
 */
function getRoleId(): ?int {
    return $_SESSION['role_id'] ?? null;
}

/**
 * Επιστρέφει το role name
 */
function getRoleName(): ?string {
    return $_SESSION['role_name'] ?? null;
}

/**
 * Ελέγχει αν ο χρήστης είναι φοιτητής
 */
function isStudent(): bool {
    return getRoleId() === 1;
}

/**
 * Ελέγχει αν ο χρήστης είναι καθηγητής
 */
function isTeacher(): bool {
    return getRoleId() === 2;
}

/**
 * Κάνει login τον χρήστη - αποθηκεύει στο session
 */
function loginUser(array $user): void {
    // Regenerate για ασφάλεια
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = (int) $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
}

/**
 * Κάνει logout τον χρήστη
 */
function logoutUser(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly'],
        );
    }

    session_destroy();
}

/**
 * Θέτει flash message για να εμφανιστεί στην επόμενη σελίδα
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Παίρνει και διαγράφει το flash message
 */
function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Ελέγχει τον κωδικό εγγραφής και επιστρέφει το role_id
 * Επιστρέφει null αν ο κωδικός είναι λάθος
 */
function validateRegistrationCode(string $code): ?int {
    if ($code === STUDENT_CODE) {
        return 1; // student
    }
    if ($code === TEACHER_CODE) {
        return 2; // teacher
    }
    return null;
}
