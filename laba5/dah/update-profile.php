<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$avatar = trim($data['avatar'] ?? '');
$bio = trim($data['bio'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Ім\'я обов\'язкове']);
    exit;
}

try {
    $db = getDbConnection();

    $stmt = $db->prepare("
        UPDATE users 
        SET name = :name, phone = :phone, picture = :avatar, bio = :bio 
        WHERE id = :user_id
    ");

    $stmt->execute([
        'name' => $name,
        'phone' => $phone,
        'avatar' => $avatar,
        'bio' => $bio,
        'user_id' => $_SESSION['user_id']
    ]);

    $_SESSION['user_name'] = $name;

    echo json_encode(['success' => true, 'message' => 'Профіль успішно оновлено']);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних']);
}
