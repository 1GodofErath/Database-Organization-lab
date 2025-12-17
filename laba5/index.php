<?php
session_start();
$theme = $_COOKIE['theme'] ?? 'light';
?>

<!DOCTYPE html> 
<html lang="uk" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бухгалтерія Online - Головна</title>
    <link rel="stylesheet" href="style/index.css">
</head>
<body>
<!-- Шапка -->
<header class="header">
    <nav class="nav">
        <div class="logo">📊 Бухгалтерія Online</div>

        <!-- Бургер-меню (тільки на мобільних) -->
        <div class="burger-menu" id="burger-menu">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </div>

        <div class="nav-links" id="nav-links">
            <button id="theme-toggle" class="theme-toggle" title="Змінити тему">
                <span class="theme-icon">🌙</span>
            </button>
            <a href="#features">Переваги</a>
            <a href="#how-it-works">Як працює</a>
            <a href="login.php">Вхід</a>
            <a href="register.php" class="btn-primary">Реєстрація</a>
        </div>
    </nav>
    <div class="nav-overlay" id="nav-overlay"></div>
</header>

<!-- Головний блок з анімованим фоном -->
<section class="hero">
    <div class="hero-background">
        <div class="hero-slide active" style="background-image: url('images/b.jpg')"></div>
        <div class="hero-slide" style="background-image: url('images/n.jpg')"></div>
        <div class="hero-slide" style="background-image: url('images/m.jpg')"></div>
        <div class="hero-slide" style="background-image: url('images/v.jpg')"></div>

    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Сучасна система обміну документами</h1>
        <p>Швидкий і безпечний обмін бухгалтерськими документами між компаніями</p>
        <div class="hero-buttons">
            <a href="register.php" class="btn">Почати безкоштовно</a>
            <a href="#features" class="btn btn-secondary">Дізнатися більше</a>
        </div>
    </div>
</section>

<!-- Переваги -->
<section class="features" id="features">
    <h2>Чому обирають нас?</h2>

    <!-- Головний блок превью -->
    <div class="feature-preview" id="feature-preview" aria-live="polite">
        <img src="images/fale.jpg" alt="Обмін файлами" id="preview-img">
        <div class="preview-content">
            <h3 id="preview-title">Обмін файлами</h3>
            <p id="preview-desc">Безпечне завантаження та передача документів між компаніями в реальному часі. Підтримка всіх популярних форматів файлів.</p>
        </div>
    </div>

    <!-- Сітка з фото-іконками -->
    <div class="feature-grid" role="list">
        <div class="feature-card active" role="button" tabindex="0"
             data-img="images/fale.jpg"
             data-title="Обмін файлами"
             data-desc="Безпечне завантаження та передача документів між компаніями в реальному часі. Підтримка всіх популярних форматів файлів.">
            <div class="feature-icon">📁</div>
            <h3>Обмін файлами</h3>
        </div>

        <div class="feature-card" role="button" tabindex="0"
             data-img="images/cat.jpg"
             data-title="Чат з партнерами"
             data-desc="Обговорюйте документи безпосередньо в системі без сторонніх месенджерів. Зручний інтерфейс для комунікації.">
            <div class="feature-icon">💬</div>
            <h3>Чат з партнерами</h3>
        </div>

        <div class="feature-card" role="button" tabindex="0"
             data-img="images/ads.jpg"
             data-title="Повна безпека"
             data-desc="Шифрування даних і захист конфіденційної бухгалтерської інформації. Відповідність стандартам безпеки.">
            <div class="feature-icon">🔒</div>
            <h3>Повна безпека</h3>
        </div>

        <div class="feature-card" role="button" tabindex="0"
             data-img="images/op.jpg"
             data-title="Історія операцій"
             data-desc="Зберігайте всі документи та листування в одному місці. Швидкий пошук та фільтрація за датою.">
            <div class="feature-icon">📊</div>
            <h3>Історія операцій</h3>
        </div>

        <div class="feature-card" role="button" tabindex="0"
             data-img="images/sup.jpg"
             data-title="Швидкість"
             data-desc="Миттєва доставка документів без затримок та втрат. Оптимізована робота навіть при повільному інтернеті.">
            <div class="feature-icon">⚡</div>
            <h3>Швидкість</h3>
        </div>

        <div class="feature-card" role="button" tabindex="0"
             data-img="images/kp.jpg"
             data-title="Командна робота"
             data-desc="Організуйте роботу декількох співробітників над документами. Розподіл прав та ролей.">
            <div class="feature-icon">👥</div>
            <h3>Командна робота</h3>
        </div>
    </div>
</section>

<!-- Як це працює -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <h2>Як це працює?</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Реєстрація</h3>
                <p>Створіть обліковий запис для вашої компанії за 2 хвилини</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Додайте партнерів</h3>
                <p>Знайдіть компанії-партнери та встановіть зв'язок</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Обмінюйтесь документами</h3>
                <p>Завантажуйте та отримуйте документи безпечно</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Обговорюйте</h3>
                <p>Вирішуйте питання в режимі реального часу</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <h2>Готові почати?</h2>
    <p>Приєднуйтесь до тисяч компаній, які вже використовують нашу систему</p>
    <a href="register.php" class="btn">Створити обліковий запис</a>
</section>

<!-- Футер -->
<footer class="footer">
    <p>&copy; 2025 Бухгалтерія Online. Всі права захищені.</p>
</footer>

<script>
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const themeIcon = document.querySelector('.theme-icon');

    const currentTheme = localStorage.getItem('theme') || getCookie('theme') || 'light';
    html.setAttribute('data-theme', currentTheme);
    updateIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
        updateIcon(newTheme);
    });

    function updateIcon(theme) {
        themeIcon.textContent = theme === 'light' ? '🌙' : '☀️';
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Бургер-меню
    (function() {
        const burger = document.getElementById('burger-menu');
        const navLinks = document.getElementById('nav-links');
        const navOverlay = document.getElementById('nav-overlay');
        const body = document.body;

        function toggleMenu() {
            burger.classList.toggle('active');
            navLinks.classList.toggle('active');
            navOverlay.classList.toggle('active');
            body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        }

        burger.addEventListener('click', toggleMenu);
        navOverlay.addEventListener('click', toggleMenu);

        // Закривати меню при кліку на посилання
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleMenu();
                }
            });
        });

        // Закривати меню при зміні розміру екрану
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
                toggleMenu();
            }
        });
    })();

    // Інтерактивний блок превью
    (function() {
        const previewImg = document.getElementById('preview-img');
        const previewTitle = document.getElementById('preview-title');
        const previewDesc = document.getElementById('preview-desc');
        const cards = document.querySelectorAll('.feature-card');

        function setActive(card) {
            cards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');

            const img = card.dataset.img;
            const title = card.dataset.title;
            const desc = card.dataset.desc;

            previewImg.style.opacity = '0';
            setTimeout(() => {
                previewImg.src = img;
                previewImg.alt = title;
                previewTitle.textContent = title;
                previewDesc.textContent = desc;
                previewImg.style.opacity = '1';
            }, 150);
        }

        cards.forEach(card => {
            card.addEventListener('click', () => setActive(card));
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setActive(card);
                }
            });
        });

        if (cards.length) setActive(cards[0]);
    })();

    // Автоматична зміна фону hero
    (function() {
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        function changeSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        if (slides.length > 1) {
            setInterval(changeSlide, 5000);
        }
    })();
</script>
</body>
</html>
