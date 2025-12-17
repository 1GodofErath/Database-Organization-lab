<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

try {
    $db = getDbConnection();

    $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Акаунт видалено']);
} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка видалення']);
}
