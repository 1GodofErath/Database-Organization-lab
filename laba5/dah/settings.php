<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../db.php';


try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $user = null;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Налаштування профілю</title>
    <link rel="stylesheet" href="/../style/settings.css">
</head>
<body>
<div class="container">
    <div class="profile-card">
        <h1>Налаштування профілю</h1>

        <div class="avatar-section">
            <img id="avatarPreview" src="<?= htmlspecialchars($user['picture'] ?? '/images/default-avatar.png') ?>" alt="Avatar">
            <button type="button" id="changeAvatarBtn" class="btn-link">Змінити фото</button>
        </div>

        <form id="profileForm" method="POST">
            <div class="form-group">
                <label for="name">Ім'я *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                <small>Email не можна змінити</small>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+380XXXXXXXXX">
            </div>

            <div class="form-group hidden">
                <label for="avatar">Аватар (URL)</label>
                <input type="url" id="avatar" name="avatar" value="<?= htmlspecialchars($user['picture'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="bio">Про себе</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Розкажіть про себе..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="spinner hidden"></span>
                    Зберегти зміни
                </button>
                <a href="dashboard.php" class="btn btn-secondary">Скасувати</a>
            </div>
        </form>

        <div id="message" class="message hidden"></div>
    </div>

    <div class="danger-zone">
        <h2>Небезпечна зона</h2>
        <button type="button" id="deleteAccountBtn" class="btn btn-danger">Видалити акаунт</button>
    </div>
</div>

<script src="/dah/js/settings.js"></script>
</body>
</html>
