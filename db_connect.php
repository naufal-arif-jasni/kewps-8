<?php
/**
 * db_connect.php
 * Sambungan Pangkalan Data MySQL / phpMyAdmin (Laragon)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kewps8_db');
define('DB_PORT', 3306);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Apabila fail dipanggil terus sebagai API
    if (strpos($_SERVER['SCRIPT_NAME'], 'api_') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode([
            'status' => 'error',
            'message' => 'Gagal menyambung ke MySQL / phpMyAdmin: ' . $e->getMessage()
        ]));
    }
}
?>
