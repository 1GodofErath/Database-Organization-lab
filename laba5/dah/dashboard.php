<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../db.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Користувач';

// Отримуємо статистику (приклад)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Система обліку</title>
    <link rel="stylesheet" href="../style/dah.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <h1>Система обліку</h1>
        <div class="nav-menu">
            <span>Вітаємо, <?= htmlspecialchars($userName) ?></span>
            <a href="../logout.php" class="btn-logout">Вийти</a>
        </div>
    </div>
</nav>

<div class="dashboard-container">
    <aside class="sidebar">
        <ul>
            <li><a href="dashboard.php" class="active">Головна</a></li>
            <li><a href="transactions.php">Транзакції</a></li>
            <li><a href="reports.php">Звіти</a></li>
            <li><a href="settings.php">Налаштування</a></li>
        </ul>
    </aside>

    <main class="content">
        <h2>Панель управління</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Всього користувачів</h3>
                <p class="stat-number"><?= $stats['total'] ?></p>
            </div>

            <div class="stat-card">
                <h3>Активні транзакції</h3>
                <p class="stat-number">0</p>
            </div>

            <div class="stat-card">
                <h3>Всього записів</h3>
                <p class="stat-number">0</p>
            </div>
        </div>

        <div class="recent-activity">
            <h3>Остання активність</h3>
            <p>Немає даних для відображення</p>
        </div>
    </main>
</div>
</body>
</html>
