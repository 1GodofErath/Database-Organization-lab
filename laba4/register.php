<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $name = trim($_POST['name']);

    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невірний email";
    }

    if (strlen($password) < 6) {
        $errors[] = "Пароль повинен містити мінімум 6 символів";
    }

    if (empty($name)) {
        $errors[] = "Введіть ім'я";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Користувач з таким email вже існує";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");

            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $_SESSION['success'] = "Реєстрація успішна! Увійдіть в систему.";
                header("Location: login.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="style/log.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-header">
        <h1>Реєстрація</h1>
        <p>Створіть новий акаунт</p>
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
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Зареєструватися</button>
        </form>
    </div>
    <div class="auth-footer">
        Вже маєте акаунт? <a href="login.php">Увійти</a>
    </div>
</div>
</body>
</html>
