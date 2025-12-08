# ❌ Обробка помилок

Довідник типових помилок MySQL та способи їх вирішення для системи обліку студентів.

## 📑 Зміст

- [Помилки таблиць](#помилки-таблиць)
- [Помилки обмежень](#помилки-обмежень)
- [Помилки зовнішніх ключів](#помилки-зовнішніх-ключів)
- [Помилки валідації](#помилки-валідації)
- [Запити для діагностики](#запити-для-діагностики)

---

## Помилки таблиць

### #1146: Table doesn't exist

**Повідомлення:**
```
ERROR 1146 (42S02): Table 'accounting.Programs' doesn't exist
```

**Причина:** Таблиця не створена або використовується неправильна база даних.

**Рішення 1:** Перевірте, чи обрана правильна база даних
```sql
-- Перевірка поточної БД
SELECT DATABASE();

-- Вибір правильної БД
USE accounting;
```

**Рішення 2:** Створіть таблицю
```sql
-- Перевірка існуючих таблиць
SHOW TABLES;

-- Якщо таблиці немає, створіть її
CREATE TABLE Programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL CHECK (CHAR_LENGTH(title) >= 3),
    UNIQUE KEY unique_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Рішення 3:** Перевірте назву таблиці
```sql
-- ❌ Невірно (без лапок для зарезервованого слова)
SELECT * FROM Groups;

-- ✅ Правильно
SELECT * FROM `Groups`;
```

---

### #1064: Syntax error near 'Groups'

**Повідомлення:**
```
ERROR 1064 (42000): You have an error in your SQL syntax; 
check the manual that corresponds to your MySQL server version 
for the right syntax to use near 'Groups' at line 1
```

**Причина:** `Groups` - зарезервоване слово в MySQL.

**Рішення:** Використовуйте зворотні лапки (`)
```sql
-- ❌ Невірно
CREATE TABLE Groups (...);
SELECT * FROM Groups;
INSERT INTO Groups (...);

-- ✅ Правильно
CREATE TABLE `Groups` (...);
SELECT * FROM `Groups`;
INSERT INTO `Groups` (...);
```

**Альтернатива:** Перейменуйте таблицю (не рекомендується)
```sql
-- Перейменування таблиці
RENAME TABLE `Groups` TO StudentGroups;
```

---

### #1050: Table already exists

**Повідомлення:**
```
ERROR 1050 (42S01): Table 'Programs' already exists
```

**Причина:** Спроба створити таблицю, яка вже існує.

**Рішення 1:** Використовуйте IF NOT EXISTS
```sql
CREATE TABLE IF NOT EXISTS Programs (...);
```

**Рішення 2:** Видаліть існуючу таблицю (будьте обережні!)
```sql
-- Перевірка існування
SHOW TABLES LIKE 'Programs';

-- Видалення з підтвердженням
DROP TABLE IF EXISTS Programs;

-- Створення нової
CREATE TABLE Programs (...);
```

**Рішення 3:** Перевірте та використовуйте існуючу
```sql
-- Перегляд структури існуючої таблиці
DESCRIBE Programs;
SHOW CREATE TABLE Programs;
```

---

## Помилки обмежень

### #1062: Duplicate entry

**Повідомлення:**
```
ERROR 1062 (23000): Duplicate entry 'Кібербезпека' 
for key 'Programs.unique_title'
```

**Причина:** Спроба додати значення, яке вже існує в полі з обмеженням UNIQUE.

**Рішення 1:** Перевірте існуючі значення
```sql
-- Для Programs.title
SELECT * FROM Programs WHERE title = 'Кібербезпека';

-- Для Groups.code
SELECT * FROM `Groups` WHERE code = 'KB-25-1';

-- Для Addresses.student_id
SELECT * FROM Addresses WHERE student_id = 1;
```

**Рішення 2:** Використовуйте інше унікальне значення
```sql
-- ❌ Дублікат
INSERT INTO Programs (title) VALUES ('Кібербезпека');

-- ✅ Унікальне значення
INSERT INTO Programs (title) VALUES ('Кібербезпека та захист інформації');
```

**Рішення 3:** Оновіть існуючий запис замість створення нового
```sql
-- Замість INSERT
UPDATE Programs 
SET title = 'Нова назва' 
WHERE title = 'Стара назва';
```

**Рішення 4:** Використовуйте INSERT ... ON DUPLICATE KEY UPDATE
```sql
INSERT INTO Programs (title) 
VALUES ('Кібербезпека')
ON DUPLICATE KEY UPDATE title = VALUES(title);
```

---

### #1048: Column cannot be null

**Повідомлення:**
```
ERROR 1048 (23000): Column 'title' cannot be null
```

**Причина:** Спроба вставити NULL в поле з обмеженням NOT NULL.

**Рішення:** Надайте значення для всіх обов'язкових полів
```sql
-- ❌ Невірно
INSERT INTO Programs (title) VALUES (NULL);

-- ✅ Правильно
INSERT INTO Programs (title) VALUES ('Назва програми');
```

**Перевірка обов'язкових полів:**
```sql
-- Перегляд структури таблиці
DESCRIBE Programs;

-- Перегляд повної інформації
SHOW CREATE TABLE Programs;
```

---

## Помилки зовнішніх ключів

### #1452: Cannot add child row (INSERT)

**Повідомлення:**
```
ERROR 1452 (23000): Cannot add or update a child row: 
a foreign key constraint fails (`accounting`.`Groups`, 
CONSTRAINT `Groups_ibfk_1` FOREIGN KEY (`program_id`) 
REFERENCES `Programs` (`id`))
```

**Причина:** Спроба додати запис з зовнішнім ключем, який не існує в батьківській таблиці.

**Рішення 1:** Перевірте існування батьківського запису
```sql
-- Для Groups (перевірка program_id)
SELECT id, title FROM Programs WHERE id = 999;

-- Для Students (перевірка group_id)
SELECT id, code FROM `Groups` WHERE id = 999;

-- Для Addresses (перевірка student_id)
SELECT id, name FROM Students WHERE id = 999;
```

**Рішення 2:** Використовуйте правильний ID
```sql
-- Перегляд доступних програм
SELECT id, title FROM Programs;

-- Додавання групи з правильним program_id
INSERT INTO `Groups` (code, program_id) VALUES ('KB-25-2', 1);
```

**Рішення 3:** Спочатку створіть батьківський запис
```sql
-- Спочатку додаємо програму
INSERT INTO Programs (title) VALUES ('Нова програма');
SET @program_id = LAST_INSERT_ID();

-- Потім додаємо групу
INSERT INTO `Groups` (code, program_id) VALUES ('NP-25-1', @program_id);
```

---

### #1451: Cannot delete parent row

**Повідомлення:**
```
ERROR 1451 (23000): Cannot delete or update a parent row: 
a foreign key constraint fails (`accounting`.`Students`, 
CONSTRAINT `Students_ibfk_1` FOREIGN KEY (`group_id`) 
REFERENCES `Groups` (`id`))
```

**Причина:** Спроба видалити запис, на який посилаються дочірні записи (через ON DELETE RESTRICT).

**Рішення 1:** Перевірте дочірні записи
```sql
-- Перевірка студентів у групі
SELECT s.id, s.name, g.code
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
WHERE g.id = 1;

-- Підрахунок студентів
SELECT g.code, COUNT(s.id) AS students_count
FROM `Groups` g
LEFT JOIN Students s ON g.id = s.group_id
WHERE g.id = 1
GROUP BY g.code;
```

**Рішення 2:** Спочатку видаліть або перемістіть дочірні записи
```sql
-- Варіант A: Перевести студентів в іншу групу
UPDATE Students SET group_id = 2 WHERE group_id = 1;

-- Варіант B: Видалити всіх студентів групи
DELETE FROM Students WHERE group_id = 1;

-- Тепер можна видалити групу
DELETE FROM `Groups` WHERE id = 1;
```

**Рішення 3:** Використовуйте каскадне видалення (потребує зміни структури)
```sql
-- Видалення існуючого обмеження
ALTER TABLE Students DROP FOREIGN KEY Students_ibfk_1;

-- Додавання нового з CASCADE
ALTER TABLE Students 
ADD CONSTRAINT Students_ibfk_1 
FOREIGN KEY (group_id) REFERENCES `Groups`(id) 
ON DELETE CASCADE ON UPDATE CASCADE;
```

---

### #1217: Cannot delete or update

**Повідомлення:**
```
ERROR 1217 (23000): Cannot delete or update a parent row: 
a foreign key constraint fails
```

**Причина:** Спроба видалити таблицю, на яку посилаються інші таблиці.

**Рішення 1:** Видаліть дочірні таблиці спочатку
```sql
-- Правильний порядок видалення таблиць
DROP TABLE IF EXISTS Addresses;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS `Groups`;
DROP TABLE IF EXISTS Programs;
```

**Рішення 2:** Відключіть перевірку зовнішніх ключів (тимчасово)
```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE Programs;
DROP TABLE `Groups`;
DROP TABLE Students;
DROP TABLE Addresses;
SET FOREIGN_KEY_CHECKS = 1;
```

⚠️ **Увага:** Другий спосіб використовувати тільки якщо ви впевнені в своїх діях!

---

## Помилки валідації

### #3819: Check constraint violated

**Повідомлення:**
```
ERROR 3819 (HY000): Check constraint 'Programs_chk_1' is violated.
```

**Причина:** Дані не відповідають CHECK обмеженню.

#### Випадок 1: Programs.title (>= 3 символи)

```sql
-- ❌ Помилка - занадто коротка назва
INSERT INTO Programs (title) VALUES ('IT');

-- ✅ Правильно
INSERT INTO Programs (title) VALUES ('ІТ+');
```

#### Випадок 2: Groups.code (REGEXP формат)

```sql
-- ❌ Помилка - невірний формат
INSERT INTO `Groups` (code, program_id) VALUES ('kb-25-1', 1);  -- маленькі літери
INSERT INTO `Groups` (code, program_id) VALUES ('KB251', 1);    -- без дефісів
INSERT INTO `Groups` (code, program_id) VALUES ('KB-25-', 1);   -- без номера

-- ✅ Правильно
INSERT INTO `Groups` (code, program_id) VALUES ('KB-25-1', 1);
INSERT INTO `Groups` (code, program_id) VALUES ('CSAI-25-10', 1);
```

**Формат коду:** `^[A-Z]{2,4}-[0-9]{2}-[0-9]+$`
- 2-4 великі латинські літери
- дефіс
- 2 цифри (рік)
- дефіс
- 1+ цифр (номер групи)

#### Випадок 3: Students.name (>= 5 символів)

```sql
-- ❌ Помилка - занадто коротке ім'я
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Іван', '2007-01-01', 'state', 1);

-- ✅ Правильно
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Іван Петренко', '2007-01-01', 'state', 1);
```

#### Випадок 4: Students.birth_date (1990-01-01 до сьогодні)

```sql
-- ❌ Помилка - дата поза діапазоном
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Старий Студент', '1989-12-31', 'state', 1);

INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Майбутній Студент', '2030-01-01', 'state', 1);

-- ✅ Правильно
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Типовий Студент', '2007-05-15', 'state', 1);
```

#### Випадок 5: Addresses.postal_code (5 цифр або NULL)

```sql
-- ❌ Помилка - невірний формат
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '0100');    -- 4 цифри
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '010011');  -- 6 цифр
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', 'ABCDE');   -- літери

-- ✅ Правильно
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '01001');
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', NULL);
```

---

### #1265: Data truncated for ENUM

**Повідомлення:**
```
ERROR 1265 (01000): Data truncated for column 'funding_type' at row 1
```

**Причина:** Значення не відповідає одному з дозволених ENUM значень.

**Рішення:** Використовуйте тільки дозволені значення
```sql
-- ❌ Помилка - невірне значення
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест', '2007-01-01', 'budget', 1);

-- ✅ Правильно (тільки 'state' або 'contract')
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест', '2007-01-01', 'state', 1);

INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест2', '2007-01-01', 'contract', 1);
```

**Перевірка дозволених значень:**
```sql
SHOW COLUMNS FROM Students LIKE 'funding_type';
```

**Результат:**
```
+--------------+---------------------------+------+-----+---------+-------+
| Field        | Type                      | Null | Key | Default | Extra |
+--------------+---------------------------+------+-----+---------+-------+
| funding_type | enum('state','contract')  | NO   |     | NULL    |       |
+--------------+---------------------------+------+-----+---------+-------+
```

---

## Запити для діагностики

### Студенти без адрес

```sql
SELECT 
    s.id,
    s.name,
    g.code AS group_code
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
LEFT JOIN Addresses a ON s.id = a.student_id
WHERE a.id IS NULL
ORDER BY s.name;
```

**Використання:** Виявити студентів, для яких потрібно додати адреси.

---

### Групи без студентів

```sql
SELECT 
    g.id,
    g.code,
    p.title AS program
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Students s ON g.id = s.group_id
WHERE s.id IS NULL
ORDER BY g.code;
```

**Використання:** Знайти порожні групи, які можна видалити.

---

### Програми без груп

```sql
SELECT 
    p.id,
    p.title
FROM Programs p
LEFT JOIN `Groups` g ON p.id = g.program_id
WHERE g.id IS NULL
ORDER BY p.title;
```

**Використання:** Виявити програми, для яких потрібно створити групи або видалити програму.

---

### Перевірка коректності віку студентів

```sql
SELECT 
    s.id,
    s.name,
    s.birth_date,
    YEAR(CURDATE()) - YEAR(s.birth_date) AS age,
    CASE 
        WHEN YEAR(CURDATE()) - YEAR(s.birth_date) < 16 THEN '⚠️ Занадто молодий'
        WHEN YEAR(CURDATE()) - YEAR(s.birth_date) > 35 THEN '⚠️ Підозріло старий'
        ELSE '✅ OK'
    END AS age_check
FROM Students s
ORDER BY age DESC;
```

**Використання:** Перевірити, чи немає аномальних віків студентів.

---

### Перевірка цілісності зовнішніх ключів

```sql
-- Перевірка Groups.program_id
SELECT 
    g.id,
    g.code,
    g.program_id,
    p.id AS program_exists
FROM `Groups` g
LEFT JOIN Programs p ON g.program_id = p.id
WHERE p.id IS NULL;

-- Перевірка Students.group_id
SELECT 
    s.id,
    s.name,
    s.group_id,
    g.id AS group_exists
FROM Students s
LEFT JOIN `Groups` g ON s.group_id = g.id
WHERE g.id IS NULL;

-- Перевірка Addresses.student_id
SELECT 
    a.id,
    a.student_id,
    s.id AS student_exists
FROM Addresses a
LEFT JOIN Students s ON a.student_id = s.id
WHERE s.id IS NULL;
```

**Використання:** Виявити порушення цілісності даних (не повинно бути результатів).

---

### Перевірка дублікатів

```sql
-- Дублікати назв програм
SELECT title, COUNT(*) AS count
FROM Programs
GROUP BY title
HAVING count > 1;

-- Дублікати кодів груп
SELECT code, COUNT(*) AS count
FROM `Groups`
GROUP BY code
HAVING count > 1;

-- Дублікати student_id в адресах
SELECT student_id, COUNT(*) AS count
FROM Addresses
GROUP BY student_id
HAVING count > 1;
```

**Використання:** Перевірити унікальність даних (не повинно бути результатів).

---

### Статистика помилок у форматі коду групи

```sql
-- Перевірка всіх кодів на відповідність формату
SELECT 
    code,
    CASE 
        WHEN code REGEXP '^[A-Z]{2,4}-[0-9]{2}-[0-9]+$' THEN '✅ Валідний'
        ELSE '❌ Невалідний'
    END AS format_check
FROM `Groups`
ORDER BY code;
```

**Використання:** Перевірити формат всіх кодів груп.

---

## 🆘 Загальні поради

### 1. Перевірка перед операцією

Завжди перевіряйте дані перед виконанням операції:

```sql
-- Перед INSERT
SELECT * FROM Programs WHERE title = 'Нова програма';

-- Перед UPDATE
SELECT * FROM Students WHERE id = 5;

-- Перед DELETE
SELECT COUNT(*) FROM Students WHERE group_id = 1;
```

### 2. Використання транзакцій

Для критичних операцій використовуйте транзакції:

```sql
START TRANSACTION;

-- Виконайте операції
INSERT INTO Programs (title) VALUES ('Тест');
INSERT INTO `Groups` (code, program_id) VALUES ('TST-25-1', LAST_INSERT_ID());

-- Перевірте результат
SELECT * FROM `Groups` WHERE code = 'TST-25-1';

-- Якщо все добре
COMMIT;

-- Якщо щось не так
-- ROLLBACK;
```

### 3. Резервне копіювання

Перед масовими змінами створюйте резервну копію:

```sql
-- Експорт через mysqldump
-- mysqldump -u root -p accounting > backup_$(date +%Y%m%d).sql

-- Резервне копіювання таблиці
CREATE TABLE Students_backup AS SELECT * FROM Students;
```

### 4. Логування змін

Ведіть лог важливих операцій:

```sql
-- Створення таблиці логів
CREATE TABLE operation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    operation_type VARCHAR(50),
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Додавання запису в лог
INSERT INTO operation_log (operation_type, table_name, record_id, description)
VALUES ('DELETE', 'Students', 5, 'Відрахування студента Петро Шевченко');
```

---

## 🔗 Корисні посилання

- [Головна сторінка](../README.md)
- [Швидкий старт](QUICK_START.md)
- [Структура БД](DATABASE_STRUCTURE.md)
- [Тестування](TESTING.md)
- [FAQ](FAQ.md)

---

**Версія:** 1.0  
**Дата оновлення:** 2025-12-08
