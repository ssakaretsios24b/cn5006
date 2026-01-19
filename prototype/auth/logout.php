<?php
/**
 * Αποσύνδεση Χρήστη
 * Καταστρέφει το session και κάνει redirect
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Κάνουμε logout
logoutUser();

// Flash message και redirect στην αρχική
setFlash('success', 'Αποσυνδέθηκες επιτυχώς!');
redirect(url(''));
