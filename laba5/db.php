<?php
function getDbConnection() {
    $host = getenv('MYSQL_HOST') ?: 'db';
    $dbname = getenv('MYSQL_DATABASE') ?: 'accounting';
    $username = getenv('MYSQL_USER') ?: 'AdminUser';
    $password = getenv('MYSQL_PASSWORD') ?: 'AminPass!2025$!';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Помилка підключення до бази даних: " . $e->getMessage());
    }
}

// Для зворотної сумісності зі старим кодом
$pdo = getDbConnection();
