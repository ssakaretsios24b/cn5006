<?php
/**
 * Helper Functions
 * Γενικές βοηθητικές συναρτήσεις
 */

/**
 * Escape HTML για αποφυγή XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect σε άλλη σελίδα
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit();
}

/**
 * Επιστρέφει το base URL της εφαρμογής
 */
function baseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // Βρίσκουμε το path μέχρι το prototype folder
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    // Αφαιρούμε subfolders αν υπάρχουν
    $basePath = preg_replace('#/(?:auth|dashboard|dashboard/.+)$#', '', $scriptPath);
    return $protocol . '://' . $host . $basePath;
}

/**
 * Επιστρέφει URL για asset (css, js, images)
 */
function asset(string $path): string {
    return baseUrl() . '/assets/' . ltrim($path, '/');
}

/**
 * Επιστρέφει URL για σελίδα
 */
function url(string $path): string {
    return baseUrl() . '/' . ltrim($path, '/');
}

/**
 * Φορματάρει ημερομηνία στα ελληνικά
 */
function formatDate(?string $date, string $format = 'd/m/Y H:i'): string {
    if (!$date) {
        return '-';
    }
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Ελέγχει αν η ημερομηνία έχει περάσει
 */
function isPastDue(?string $dueDate): bool {
    if (!$dueDate) {
        return false;
    }
    return strtotime($dueDate) < time();
}

/**
 * Επιστρέφει χρώμα badge ανάλογα με τον βαθμό
 */
function gradeColor(?float $grade): string {
    if ($grade === null) {
        return 'secondary';
    }
    if ($grade >= 8.5) {
        return 'success';
    }
    if ($grade >= 5) {
        return 'primary';
    }
    return 'danger';
}

/**
 * Δημιουργεί CSRF token
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Επιστρέφει hidden input με CSRF token
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Ελέγχει CSRF token
 */
function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Παίρνει τιμή από POST και την καθαρίζει
 */
function post(string $key, $default = ''): string {
    $value = $_POST[$key] ?? $default;
    return trim($value);
}

/**
 * Παίρνει τιμή από GET
 */
function get(string $key, $default = ''): string {
    $value = $_GET[$key] ?? $default;
    return trim($value);
}

/**
 * Ελέγχει αν το request είναι POST
 * Κάνει αυτόματα CSRF verification
 */
function isPost(): bool {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    // Έλεγχος CSRF token
    if (!verifyCsrf()) {
        setFlash('error', 'Μη έγκυρο αίτημα. Δοκιμάστε ξανά.');
        redirect($_SERVER['REQUEST_URI']);
    }

    return true;
}

/**
 * Μετράει αν έχει περάσει η προθεσμία και πόσες μέρες
 */
function daysUntilDue(?string $dueDate): ?int {
    if (!$dueDate) {
        return null;
    }
    $now = new DateTime();
    $due = new DateTime($dueDate);
    $diff = $now->diff($due);
    return $diff->invert ? -$diff->days : $diff->days;
}
