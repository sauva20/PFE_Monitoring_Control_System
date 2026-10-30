<?php
// db.php - MySQL Database Connection Configuration

$db_host = 'localhost';
$db_port = '3306';
$db_name = 'first_aid_db';
$db_user = 'root';
$db_pass = ''; // Default Laragon/XAMPP biasanya tanpa password

try {
    $pdo = new PDO(
        "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi Database MySQL Gagal: ' . $e->getMessage() . '. Pastikan database `first_aid_db` sudah dibuat & file `database.sql` telah di-import ke MySQL (Laragon/phpMyAdmin).'
    ]);
    exit;
}
