# ⚙️ CRUD операції

Детальний довідник SQL-запитів для виконання операцій створення, читання, оновлення та видалення (CRUD) в системі обліку студентів.

## 📑 Зміст

- [CREATE (Створення)](#create-створення)
- [READ (Читання)](#read-читання)
- [UPDATE (Оновлення)](#update-оновлення)
- [DELETE (Видалення)](#delete-видалення)

---

## CREATE (Створення)

### 1. Додати програму

**Базовий приклад:**
```sql
INSERT INTO Programs (title) 
VALUES ('Штучний інтелект');
```

**З вказаним ID:**
```sql
INSERT INTO Programs (id, title) 
VALUES (4, 'Системний аналіз');
```

**Масове додавання:**
```sql
INSERT INTO Programs (title) VALUES 
    ('Штучний інтелект'),
    ('Системний аналіз'),
    ('Інформаційні системи');
```

**Очікуваний результат:**
```
Query OK, 1 row affected (0.01 sec)
```

**Перевірка:**
```sql
SELECT * FROM Programs ORDER BY id DESC LIMIT 1;
```

---

### 2. Додати групу

**Базовий приклад:**
```sql
INSERT INTO `Groups` (code, program_id) 
VALUES ('AI-25-1', 4);
```

⚠️ **Важливо:** Використовуйте зворотні лапки для `Groups`.

**З перевіркою існування програми:**
```sql
-- Спочатку перевіримо програму
SELECT id, title FROM Programs WHERE id = 4;

-- Якщо програма існує, додаємо групу
INSERT INTO `Groups` (code, program_id) 
VALUES ('AI-25-1', 4);
```

**Масове додавання:**
```sql
INSERT INTO `Groups` (code, program_id) VALUES 
    ('AI-25-1', 4),
    ('AI-25-2', 4),
    ('SA-25-1', 5);
```

**Формат коду групи:**
- Літери (2-4): Абревіатура програми
- Цифри (2): Рік вступу
- Цифри (1+): Номер групи
- Приклад: `AI-25-1`, `CSAI-24-2`

---

### 3. Додати студента

**Базовий приклад (бюджет):**
```sql
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Оксана Мельник', '2007-06-20', 'state', 1);
```

**Контрактник:**
```sql
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Віктор Коваленко', '2006-09-15', 'contract', 2);
```

**З перевіркою групи:**
```sql
-- Перевірка існування групи
SELECT id, code FROM `Groups` WHERE id = 1;

-- Додавання студента
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Оксана Мельник', '2007-06-20', 'state', 1);
```

**Масове додавання:**
```sql
INSERT INTO Students (name, birth_date, funding_type, group_id) VALUES 
    ('Оксана Мельник', '2007-06-20', 'state', 1),
    ('Віктор Коваленко', '2006-09-15', 'contract', 2),
    ('Тетяна Литвин', '2007-02-11', 'state', 3);
```

**Збереження ID нового студента:**
```sql
-- Після INSERT можна отримати ID
SELECT LAST_INSERT_ID();
```

---

### 4. Додати адресу

**Базовий приклад:**
```sql
INSERT INTO Addresses (student_id, city, street, postal_code) 
VALUES (6, 'Харків', 'вул. Сумська, 45', '61000');
```

**Без вулиці та індексу:**
```sql
INSERT INTO Addresses (student_id, city) 
VALUES (7, 'Одеса');
```

**Тільки з вулицею:**
```sql
INSERT INTO Addresses (student_id, city, street) 
VALUES (8, 'Дніпро', 'просп. Гагаріна, 100');
```

**З перевіркою студента:**
```sql
-- Перевірка, чи студент існує і чи немає у нього адреси
SELECT s.id, s.name, a.id AS address_id 
FROM Students s 
LEFT JOIN Addresses a ON s.id = a.student_id 
WHERE s.id = 6;

-- Якщо address_id = NULL, можна додати адресу
INSERT INTO Addresses (student_id, city, street, postal_code) 
VALUES (6, 'Харків', 'вул. Сумська, 45', '61000');
```

**Масове додавання:**
```sql
INSERT INTO Addresses (student_id, city, street, postal_code) VALUES 
    (6, 'Харків', 'вул. Сумська, 45', '61000'),
    (7, 'Одеса', 'вул. Дерибасівська, 1', '65000'),
    (8, 'Дніпро', 'просп. Гагаріна, 100', '49000');
```

---

## READ (Читання)

### 1. Переглянути всі програми

**Базовий запит:**
```sql
SELECT * FROM Programs;
```

**З сортуванням:**
```sql
SELECT * FROM Programs ORDER BY title ASC;
```

**З кількістю груп:**
```sql
SELECT p.id, p.title, COUNT(g.id) AS groups_count
FROM Programs p
LEFT JOIN `Groups` g ON p.id = g.program_id
GROUP BY p.id, p.title
ORDER BY p.title;
```

**Очікуваний результат:**
```
+----+---------------------+--------------+
| id | title               | groups_count |
+----+---------------------+--------------+
|  3 | Комп'ютерні науки   |            1 |
|  1 | Кібербезпека        |            1 |
|  2 | Інженерія ПЗ        |            1 |
+----+---------------------+--------------+
```

---

### 2. Переглянути групи з назвами програм

**Базовий запит:**
```sql
SELECT g.id, g.code, p.title AS program_title
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
ORDER BY g.code;
```

**З кількістю студентів:**
```sql
SELECT 
    g.id, 
    g.code, 
    p.title AS program_title,
    COUNT(s.id) AS students_count
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY g.id, g.code, p.title
ORDER BY g.code;
```

**Очікуваний результат:**
```
+----+----------+---------------------+----------------+
| id | code     | program_title       | students_count |
+----+----------+---------------------+----------------+
|  3 | CS-25-1  | Комп'ютерні науки   |              1 |
|  2 | IPZ-25-1 | Інженерія ПЗ        |              2 |
|  1 | KB-25-1  | Кібербезпека        |              2 |
+----+----------+---------------------+----------------+
```

---

### 3. Переглянути студентів з повною інформацією

**Повна інформація (JOIN з усіма таблицями):**
```sql
SELECT 
    s.id,
    s.name,
    s.birth_date,
    YEAR(CURDATE()) - YEAR(s.birth_date) AS age,
    s.funding_type,
    g.code AS group_code,
    p.title AS program_title,
    a.city,
    a.street,
    a.postal_code
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Addresses a ON s.id = a.student_id
ORDER BY s.name;
```

**З українськими назвами типів фінансування:**
```sql
SELECT 
    s.id,
    s.name,
    s.birth_date,
    CASE 
        WHEN s.funding_type = 'state' THEN 'Бюджет'
        WHEN s.funding_type = 'contract' THEN 'Контракт'
    END AS funding_type_ua,
    g.code AS group_code,
    p.title AS program_title
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
ORDER BY s.name;
```

**Очікуваний результат:**
```
+----+-----------------+------------+------------------+------------+---------------------+
| id | name            | birth_date | funding_type_ua  | group_code | program_title       |
+----+-----------------+------------+------------------+------------+---------------------+
|  1 | Анна Коваль     | 2007-03-14 | Контракт         | KB-25-1    | Кібербезпека        |
|  2 | Іван Петренко   | 2006-11-02 | Бюджет           | KB-25-1    | Кібербезпека        |
|  3 | Марія Бондар    | 2007-08-22 | Бюджет           | IPZ-25-1   | Інженерія ПЗ        |
|  4 | Олег Сидоренко  | 2006-01-30 | Контракт         | IPZ-25-1   | Інженерія ПЗ        |
|  5 | Петро Шевченко  | 2007-05-18 | Бюджет           | CS-25-1    | Комп'ютерні науки   |
+----+-----------------+------------+------------------+------------+---------------------+
```

---

### 4. Знайти студента за ID

**Базовий запит:**
```sql
SELECT * FROM Students WHERE id = 1;
```

**З повною інформацією:**
```sql
SELECT 
    s.*,
    g.code AS group_code,
    p.title AS program_title,
    a.city,
    a.street,
    a.postal_code
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Addresses a ON s.id = a.student_id
WHERE s.id = 1;
```

---

### 5. Пошук студента за іменем

**Точний пошук:**
```sql
SELECT * FROM Students WHERE name = 'Іван Петренко';
```

**Пошук за частиною імені (LIKE):**
```sql
SELECT * FROM Students WHERE name LIKE '%Іван%';
```

**Пошук за прізвищем (останнє слово):**
```sql
SELECT * FROM Students WHERE name LIKE '%Петренко';
```

**Пошук без урахування регістру (COLLATE):**
```sql
SELECT * FROM Students 
WHERE name COLLATE utf8mb4_unicode_ci LIKE '%іван%';
```

**З додатковою інформацією:**
```sql
SELECT 
    s.id,
    s.name,
    g.code AS group_code,
    p.title AS program_title
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
WHERE s.name LIKE '%Іван%';
```

---

## UPDATE (Оновлення)

### 1. Оновити назву програми

**Базовий приклад:**
```sql
UPDATE Programs 
SET title = 'Кібербезпека та захист інформації' 
WHERE id = 1;
```

**З перевіркою:**
```sql
-- Перевірка поточного значення
SELECT * FROM Programs WHERE id = 1;

-- Оновлення
UPDATE Programs 
SET title = 'Кібербезпека та захист інформації' 
WHERE id = 1;

-- Перевірка результату
SELECT * FROM Programs WHERE id = 1;
```

**Очікуваний результат:**
```
Query OK, 1 row affected (0.01 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```

---

### 2. Змінити код групи

**Базовий приклад:**
```sql
UPDATE `Groups` 
SET code = 'KB-25-2' 
WHERE id = 1;
```

**З перевіркою унікальності:**
```sql
-- Перевірка, чи код вже існує
SELECT * FROM `Groups` WHERE code = 'KB-25-2';

-- Якщо не існує, оновлюємо
UPDATE `Groups` 
SET code = 'KB-25-2' 
WHERE id = 1;
```

---

### 3. Перевести студента на контракт

**Один студент:**
```sql
UPDATE Students 
SET funding_type = 'contract' 
WHERE id = 2;
```

**Всі студенти групи:**
```sql
UPDATE Students 
SET funding_type = 'contract' 
WHERE group_id = 1;
```

**З перевіркою:**
```sql
-- Перевірка поточного стану
SELECT id, name, funding_type FROM Students WHERE id = 2;

-- Оновлення
UPDATE Students 
SET funding_type = 'contract' 
WHERE id = 2;

-- Перевірка результату
SELECT id, name, funding_type FROM Students WHERE id = 2;
```

**Переведення з бюджету на контракт:**
```sql
UPDATE Students 
SET funding_type = 'contract' 
WHERE id = 2 AND funding_type = 'state';
```

---

### 4. Перевести студента в іншу групу

**Базовий приклад:**
```sql
UPDATE Students 
SET group_id = 3 
WHERE id = 1;
```

**З перевіркою групи:**
```sql
-- Перевірка існування групи
SELECT id, code FROM `Groups` WHERE id = 3;

-- Поточна група студента
SELECT s.id, s.name, g.code AS current_group
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
WHERE s.id = 1;

-- Переведення
UPDATE Students 
SET group_id = 3 
WHERE id = 1;

-- Перевірка результату
SELECT s.id, s.name, g.code AS new_group
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
WHERE s.id = 1;
```

**Переведення всіх студентів з однієї групи в іншу:**
```sql
UPDATE Students 
SET group_id = 3 
WHERE group_id = 1;
```

---

### 5. Оновити адресу

**Оновити місто:**
```sql
UPDATE Addresses 
SET city = 'Київ' 
WHERE student_id = 1;
```

**Оновити всю адресу:**
```sql
UPDATE Addresses 
SET 
    city = 'Київ',
    street = 'вул. Хрещатик, 1',
    postal_code = '01001'
WHERE student_id = 1;
```

**Додати поштовий індекс:**
```sql
UPDATE Addresses 
SET postal_code = '14000' 
WHERE student_id = 1 AND postal_code IS NULL;
```

**Очистити вулицю:**
```sql
UPDATE Addresses 
SET street = NULL 
WHERE student_id = 1;
```

---

## DELETE (Видалення)

### 1. Видалити програму (з каскадом)

**Базове видалення:**
```sql
DELETE FROM Programs WHERE id = 4;
```

⚠️ **Увага:** Це автоматично видалить всі групи цієї програми через `ON DELETE CASCADE`.

**З попередженням:**
```sql
-- Перевірка, скільки груп буде видалено
SELECT 
    p.id AS program_id,
    p.title,
    COUNT(g.id) AS groups_to_delete
FROM Programs p
LEFT JOIN `Groups` g ON p.id = g.program_id
WHERE p.id = 4
GROUP BY p.id, p.title;

-- Якщо впевнені, видаляємо
DELETE FROM Programs WHERE id = 4;
```

**Результат:**
```
-- Видалиться програма і всі її групи
Query OK, 1 row affected (0.02 sec)
```

---

### 2. Видалити групу (з перевіркою)

**Спроба видалити групу зі студентами:**
```sql
DELETE FROM `Groups` WHERE id = 1;
```

**Помилка:**
```
ERROR 1451 (23000): Cannot delete or update a parent row: 
a foreign key constraint fails
```

**Правильний підхід - спочатку перевірити:**
```sql
-- Перевірка наявності студентів
SELECT 
    g.id,
    g.code,
    COUNT(s.id) AS students_count
FROM `Groups` g
LEFT JOIN Students s ON g.id = s.group_id
WHERE g.id = 1
GROUP BY g.id, g.code;

-- Якщо students_count = 0, можна видаляти
DELETE FROM `Groups` WHERE id = 1;
```

**Видалення групи після переведення студентів:**
```sql
-- Крок 1: Перевести студентів в іншу групу
UPDATE Students SET group_id = 2 WHERE group_id = 1;

-- Крок 2: Видалити порожню групу
DELETE FROM `Groups` WHERE id = 1;
```

---

### 3. Видалити студента

**Базове видалення:**
```sql
DELETE FROM Students WHERE id = 5;
```

⚠️ **Увага:** Адреса студента буде автоматично видалена через `ON DELETE CASCADE`.

**З перевіркою:**
```sql
-- Перевірка інформації про студента
SELECT 
    s.id,
    s.name,
    a.id AS address_id,
    a.city
FROM Students s
LEFT JOIN Addresses a ON s.id = a.student_id
WHERE s.id = 5;

-- Видалення
DELETE FROM Students WHERE id = 5;
```

**Видалити кількох студентів:**
```sql
-- За списком ID
DELETE FROM Students WHERE id IN (5, 6, 7);

-- За групою
DELETE FROM Students WHERE group_id = 3;

-- За типом фінансування
DELETE FROM Students WHERE funding_type = 'contract';
```

---

### 4. Видалити адресу

**Базове видалення:**
```sql
DELETE FROM Addresses WHERE student_id = 1;
```

**З перевіркою:**
```sql
-- Перевірка існування
SELECT * FROM Addresses WHERE student_id = 1;

-- Видалення
DELETE FROM Addresses WHERE student_id = 1;

-- Перевірка результату
SELECT * FROM Addresses WHERE student_id = 1;
```

**Видалити адреси з певного міста:**
```sql
DELETE FROM Addresses WHERE city = 'Київ';
```

**Видалити адреси без індексу:**
```sql
DELETE FROM Addresses WHERE postal_code IS NULL;
```

---

## 📊 Підсумкові операції

### Підрахунок записів

**Кількість записів у кожній таблиці:**
```sql
SELECT 'Programs' AS table_name, COUNT(*) AS count FROM Programs
UNION ALL
SELECT 'Groups', COUNT(*) FROM `Groups`
UNION ALL
SELECT 'Students', COUNT(*) FROM Students
UNION ALL
SELECT 'Addresses', COUNT(*) FROM Addresses;
```

### Транзакції

**Приклад використання транзакції:**
```sql
START TRANSACTION;

-- Додати програму
INSERT INTO Programs (title) VALUES ('Нова програма');
SET @program_id = LAST_INSERT_ID();

-- Додати групу
INSERT INTO `Groups` (code, program_id) VALUES ('NP-25-1', @program_id);

-- Якщо все добре, зберегти
COMMIT;

-- Або відмінити при помилці
-- ROLLBACK;
```

---

## 🔗 Корисні посилання

- [Головна сторінка](../README.md)
- [Швидкий старт](QUICK_START.md)
- [Структура БД](DATABASE_STRUCTURE.md)
- [Типові сценарії](USE_CASES.md)
- [FAQ](FAQ.md)

---

**Версія:** 1.0  
**Дата оновлення:** 2025-12-08
