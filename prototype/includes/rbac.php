<?php
/**
 * RBAC - Role Based Access Control
 * Έλεγχος πρόσβασης με βάση τον ρόλο
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/**
 * Απαιτεί ο χρήστης να είναι συνδεδεμένος
 * Αλλιώς redirect στο login
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Πρέπει να συνδεθείτε για να δείτε αυτή τη σελίδα.');
        redirect(url('auth/login.php'));
    }
}

/**
 * Απαιτεί ο χρήστης να είναι φοιτητής
 */
function requireStudent(): void {
    requireLogin();
    if (!isStudent()) {
        showForbidden();
    }
}

/**
 * Απαιτεί ο χρήστης να είναι καθηγητής
 */
function requireTeacher(): void {
    requireLogin();
    if (!isTeacher()) {
        showForbidden();
    }
}

/**
 * Εμφανίζει σελίδα Forbidden Action
 */
function showForbidden(): void {
    http_response_code(403); ?>
    <!DOCTYPE html>
    <html lang="el">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forbidden Action - Archimedes University</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --primary: #1a365d;
                --secondary: #c9a227;
            }
            body {
                background: linear-gradient(135deg, var(--primary) 0%, #0f2442 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .forbidden-card {
                background: white;
                border-radius: 1rem;
                padding: 3rem;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            .forbidden-icon {
                font-size: 5rem;
                color: #dc2626;
            }
            .btn-back {
                background: var(--primary);
                border-color: var(--primary);
            }
            .btn-back:hover {
                background: #0f2442;
            }
        </style>
    </head>
    <body>
        <div class="forbidden-card">
            <div class="forbidden-icon">⛔</div>
            <h1 class="h2 mt-3 mb-3">Forbidden Action</h1>
            <p class="text-muted mb-4">
                Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.<br>
                Η ενέργεια αυτή δεν επιτρέπεται για τον ρόλο σας.
            </p>
            <a href="<?= url('dashboard/') ?>" class="btn btn-back btn-lg text-white">
                Επιστροφή στο Dashboard
            </a>
        </div>
    </body>
    </html>
    <?php exit();
}

/**
 * Απαιτεί ο χρήστης να ΜΗΝ είναι συνδεδεμένος
 * Χρήσιμο για login/register pages
 */
function requireGuest(): void {
    if (isLoggedIn()) {
        redirect(url('dashboard/'));
    }
}
