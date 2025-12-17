<?php
session_start();

// Генерація CSRF токену
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'db.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF токену
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Невірний CSRF токен");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $name = trim($_POST['name']);

    // Валідація імені
    if (empty($name)) {
        $errors[] = "Введіть ім'я";
    } elseif (mb_strlen($name) < 2) {
        $errors[] = "Ім'я повинно містити мінімум 2 символи";
    } elseif (mb_strlen($name) > 255) {
        $errors[] = "Ім'я не може бути довшим за 255 символів";
    }

    // Валідація email
    if (empty($email)) {
        $errors[] = "Введіть email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невірний формат email";
    }

    // Валідація пароля
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
    if (empty($password_confirm)) {
        $errors[] = "Підтвердіть пароль";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Паролі не співпадають";
    }

    // Якщо валідація пройшла успішно
    if (empty($errors)) {
        try {
            // Перевірка чи email вже існує
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = "Користувач з таким email вже існує";
            } else {
                // Реєстрація нового користувача
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");

                if ($stmt->execute([$name, $email, $hashedPassword])) {
                    // Логування успішної реєстрації
                    error_log("New user registered: " . $email);

                    $_SESSION['success'] = "Реєстрація успішна! Увійдіть в систему.";
                    header("Location: login.php");
                    exit;
                } else {
                    $errors[] = "Помилка при реєстрації. Спробуйте пізніше.";
                }
            }
        } catch (PDOException $e) {
            // Логування помилки БД
            error_log("Register DB error: " . $e->getMessage());
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
    <title>Реєстрація</title>
    <link rel="stylesheet" href="style/log.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-header">
        <a href="index.php" class="back-button">← Назад на головну</a>
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

        <form method="POST" id="register-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label>Ім'я</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required minlength="2" maxlength="255">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" id="password" class="form-control" minlength="8" maxlength="255" required>
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
                <label>Підтвердіть пароль</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" minlength="8" maxlength="255" required>
                <small class="form-text" id="password-match"></small>
            </div>

            <button type="submit" class="btn btn-primary">Зареєструватися</button>
        </form>
    </div>
    <div class="auth-footer">
        Вже маєте акаунт? <a href="login.php">Увійти</a>
    </div>
</div>

<script>
    (function() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
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

            // Мінімум 8 символів
            if (value.length >= 8) {
                requirements.length.classList.add('valid');
                validCount++;
            } else {
                requirements.length.classList.remove('valid');
            }

            // Велика літера (англійські A-Z та українські А-Я)
            if (/[A-ZА-ЯІЇЄҐ]/.test(value)) {
                requirements.uppercase.classList.add('valid');
                validCount++;
            } else {
                requirements.uppercase.classList.remove('valid');
            }

            // Мала літера (англійські a-z та українські а-я)
            if (/[a-zа-яіїєґ]/.test(value)) {
                requirements.lowercase.classList.add('valid');
                validCount++;
            } else {
                requirements.lowercase.classList.remove('valid');
            }

            // Цифра
            if (/\d/.test(value)) {
                requirements.number.classList.add('valid');
                validCount++;
            } else {
                requirements.number.classList.remove('valid');
            }

            // Спеціальний символ
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                requirements.special.classList.add('valid');
                validCount++;
            } else {
                requirements.special.classList.remove('valid');
            }

            // Оновлення прогрес-бару
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
