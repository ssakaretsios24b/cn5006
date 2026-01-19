<?php
/**
 * Database Configuration
 * Σύνδεση στη MariaDB μέσω PDO
 */

// Ρυθμίσεις βάσης
define('DB_HOST', 'localhost');
define('DB_NAME', 'archimedes');
define('DB_USER', 'archimedes');
define('DB_PASS', 'archimedes123');
define('DB_CHARSET', 'utf8mb4');

/**
 * Επιστρέφει ένα PDO connection
 * Χρησιμοποιεί singleton pattern για να μην ανοίγει πολλά connections
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Στο production θα έκρυβε το μήνυμα
            die('Σφάλμα σύνδεσης στη βάση: ' . $e->getMessage());
        }
    }

    return $pdo;
}
