<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php';

$googleConfig = require __DIR__ . '/api/google-config.php';

$client = new Google_Client();
$client->setClientId($googleConfig['client_id']);
$client->setClientSecret($googleConfig['client_secret']);
$client->setRedirectUri($googleConfig['redirect_uri']);
$client->addScope("email");
$client->addScope("profile");

$googleLoginUrl = $client->createAuthUrl();

$errors = [];
$email = '';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Невірний CSRF токен");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невірний формат email";
    }

    if (strlen($password) < 8 || strlen($password) > 255) {
        $errors[] = "Пароль повинен містити від 8 до 255 символів";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                error_log("User logged in: " . $email);

                header("Location: dah/dashboard.php");
                exit;
            } else {
                $errors[] = "Невірний email або пароль";
            }
        } catch (PDOException $e) {
            error_log("Login DB error: " . $e->getMessage());
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
    <title>Вхід</title>
    <link rel="stylesheet" href="style/log.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-header">
        <a href="index.php" class="back-button">← Повернутися на головну</a>
        <h1>Вхід</h1>
        <p>Увійдіть у свій акаунт</p>
    </div>
    <div class="auth-body">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary">Увійти</button>
        </form>

        <div class="divider">
            <span>або</span>
        </div>

        <div class="google-login">
            <a href="<?= htmlspecialchars($googleLoginUrl) ?>" class="btn btn-google">
                <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Увійти через Google
            </a>
        </div>
    </div>
    <div class="auth-footer">
        Немає акаунту? <a href="register.php">Зареєструватися</a>
    </div>
</div>
</body>
</html>
