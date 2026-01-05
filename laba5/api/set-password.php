<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Генерація CSRF токену
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['google_user'])) {
    header("Location: /login.php");
    exit;
}

// Підключення до БД та отримання об'єкта $pdo
require_once __DIR__ . '/../db.php';


// HTTP заголовки безпеки
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");

$googleUser = $_SESSION['google_user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF захист
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Невірний CSRF токен");
    }

    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Валідація імені
    if (empty($name)) {
        $errors[] = "Введіть ім'я";
    } elseif (mb_strlen($name) < 2) {
        $errors[] = "Ім'я повинно містити мінімум 2 символи";
    } elseif (mb_strlen($name) > 255) {
        $errors[] = "Ім'я не може бути довшим за 255 символів";
    }

    // Валідація пароля (ідентична register.php)
    if (empty($password)) {
        $errors[] = "Введіть пароль";
    } elseif (mb_strlen($password, 'UTF-8') < 8) {
        $errors[] = "Пароль повинен містити мінімум 8 символів";
    } elseif (mb_strlen($password, 'UTF-8') > 255) {
        $errors[] = "Пароль занадто довгий";
    } elseif (!preg_match('/[A-ZА-ЯІЇЄҐ]/u', $password)) {
        $errors[] = "Пароль повинен містити хоча б одну велику літеру (А-Я, A-Z)";
    } elseif (!preg_match('/[a-zа-яіїєґ]/u', $password)) {
        $errors[] = "Пароль повинен містити хоча б одну малу літеру (а-я, a-z)";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Пароль повинен містити хоча б одну цифру (0-9)";
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = "Пароль повинен містити хоча б один спеціальний символ (!@#$%^&*)";
    }

    // Перевірка підтвердження пароля
    if (empty($confirmPassword)) {
        $errors[] = "Підтвердіть пароль";
    } elseif ($password !== $confirmPassword) {
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

            // Регенерація session ID
            session_regenerate_id(true);

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Встановлення пароля</title>
    <link rel="stylesheet" href="../style/log.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-header">
        <h1>Завершення реєстрації</h1>
        <p>Встановіть ім'я та пароль для вашого акаунту</p>
        <small>Email: <?= htmlspecialchars($googleUser['email'], ENT_QUOTES, 'UTF-8') ?></small>
    </div>
    <div class="auth-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="set-password-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="name">Ім'я</label>
                <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($googleUser['name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                        minlength="2"
                        maxlength="255"
                        autocomplete="name"
                >
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        maxlength="255"
                        required
                        autocomplete="new-password"
                >
                <div class="password-requirements">
                    <small>Вимоги до пароля:</small>
                    <div class="password-strength">
                        <div class="password-strength-bar"></div>
                    </div>
                    <ul>
                        <li id="req-length">Мінімум 8 символів</li>
                        <li id="req-uppercase">Велика літера (А-Я, A-Z)</li>
                        <li id="req-lowercase">Мала літера (а-я, a-z)</li>
                        <li id="req-number">Цифра (0-9)</li>
                        <li id="req-special">Спеціальний символ (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Підтвердження пароля</label>
                <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        minlength="8"
                        maxlength="255"
                        required
                        autocomplete="new-password"
                >
                <small class="form-text" id="password-match"></small>
            </div>

            <button type="submit" class="btn btn-primary">Завершити реєстрацію</button>
        </form>
    </div>
</div>

<script>
    (function() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('confirm_password');
        const matchText = document.getElementById('password-match');
        const requirementsDiv = document.querySelector('.password-requirements');

        const requirements = {
            length: document.getElementById('req-length'),
            uppercase: document.getElementById('req-uppercase'),
            lowercase: document.getElementById('req-lowercase'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };

        const strengthBar = document.querySelector('.password-strength-bar');

        password.addEventListener('focus', () => {
            requirementsDiv.classList.add('active');
        });

        password.addEventListener('blur', () => {
            setTimeout(() => {
                requirementsDiv.classList.remove('active');
            }, 200);
        });

        function validatePassword() {
            const value = password.value;
            let validCount = 0;

            if (value.length >= 8) {
                requirements.length.classList.add('valid');
                validCount++;
            } else {
                requirements.length.classList.remove('valid');
            }

            if (/[A-ZА-ЯІЇЄҐ]/.test(value)) {
                requirements.uppercase.classList.add('valid');
                validCount++;
            } else {
                requirements.uppercase.classList.remove('valid');
            }

            if (/[a-zа-яіїєґ]/.test(value)) {
                requirements.lowercase.classList.add('valid');
                validCount++;
            } else {
                requirements.lowercase.classList.remove('valid');
            }

            if (/\d/.test(value)) {
                requirements.number.classList.add('valid');
                validCount++;
            } else {
                requirements.number.classList.remove('valid');
            }

            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                requirements.special.classList.add('valid');
                validCount++;
            } else {
                requirements.special.classList.remove('valid');
            }

            const percentage = (validCount / 5) * 100;
            strengthBar.style.width = percentage + '%';

            if (validCount <= 2) {
                strengthBar.style.background = 'linear-gradient(90deg, #f56565, #ed8936)';
            } else if (validCount <= 3) {
                strengthBar.style.background = 'linear-gradient(90deg, #ed8936, #ecc94b)';
            } else if (validCount <= 4) {
                strengthBar.style.background = 'linear-gradient(90deg, #ecc94b, #48bb78)';
            } else {
                strengthBar.style.background = 'linear-gradient(90deg, #48bb78, #38b2ac)';
            }
        }

        function checkPasswordMatch() {
            if (passwordConfirm.value === '') {
                matchText.textContent = '';
                matchText.className = 'form-text';
                return;
            }

            if (password.value === passwordConfirm.value) {
                matchText.textContent = '✓ Паролі співпадають';
                matchText.className = 'form-text match-success';
            } else {
                matchText.textContent = '✗ Паролі не співпадають';
                matchText.className = 'form-text match-error';
            }
        }

        password.addEventListener('input', validatePassword);
        password.addEventListener('input', checkPasswordMatch);
        passwordConfirm.addEventListener('input', checkPasswordMatch);
    })();
</script>
</body>
</html>
