<?php
session_start();

if (!isset($_SESSION['google_user'])) {
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

$googleUser = $_SESSION['google_user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($name) || mb_strlen($name) > 255) {
        $errors[] = "Введіть ім'я (макс. 255 символів)";
    }

    if (strlen($password) < 8 || strlen($password) > 255) {
        $errors[] = "Пароль повинен містити від 8 до 255 символів";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Паролі не співпадають";
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            if (isset($googleUser['user_id'])) {
                $stmt = $pdo->prepare("UPDATE users SET password = ?, name = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $name, $googleUser['user_id']]);
                $userId = $googleUser['user_id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, google_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $googleUser['email'], $hashedPassword, $googleUser['google_id']]);
                $userId = $pdo->lastInsertId();
            }

            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $googleUser['email'];

            unset($_SESSION['google_user']);

            error_log("User completed Google registration: " . $googleUser['email']);
            header("Location: ../dah/dashboard.php");
            exit;
        } catch (PDOException $e) {
            error_log("Set password DB error: " . $e->getMessage());
            $errors[] = "Помилка системи. Спробуйте пізніше.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення пароля</title>
    <link rel="stylesheet" href="../style/log.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-header">
        <h1>Завершення реєстрації</h1>
        <p>Встановіть ім'я та пароль для вашого акаунту</p>
        <small>Email: <?= htmlspecialchars($googleUser['email']) ?></small>
    </div>
    <div class="auth-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Ім'я</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($googleUser['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
                <small class="form-text">Мінімум 8 символів</small>
            </div>

            <div class="form-group">
                <label>Підтвердження пароля</label>
                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary">Завершити реєстрацію</button>
        </form>
    </div>
</div>
</body>
</html>