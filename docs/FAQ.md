# ❓ Часті питання (FAQ)

Відповіді на найпоширеніші питання про систему обліку студентів.

## 📑 Зміст

- [Загальні питання](#загальні-питання)
- [Технічні питання](#технічні-питання)
- [Запити та операції](#запити-та-операції)
- [Корисні запити](#корисні-запити)

---

## Загальні питання

### Q1: Як скинути AUTO_INCREMENT?

**Відповідь:** Використовуйте ALTER TABLE для скидання лічильника.

```sql
-- Скинути до 1
ALTER TABLE Programs AUTO_INCREMENT = 1;

-- Скинути до наступного значення після максимального
ALTER TABLE Programs AUTO_INCREMENT = (SELECT MAX(id) + 1 FROM Programs);

-- Для всіх таблиць
ALTER TABLE Programs AUTO_INCREMENT = 1;
ALTER TABLE `Groups` AUTO_INCREMENT = 1;
ALTER TABLE Students AUTO_INCREMENT = 1;
ALTER TABLE Addresses AUTO_INCREMENT = 1;
```

**Важливо:** Це можна робити тільки якщо таблиця порожня або потрібно продовжити з конкретного номера.

---

### Q2: Як експортувати дані?

**Відповідь:** Є декілька способів експорту даних.

#### Спосіб 1: mysqldump (повний експорт)

```bash
# Експорт всієї БД
mysqldump -u root -p accounting > accounting_backup.sql

# Експорт тільки структури (без даних)
mysqldump -u root -p --no-data accounting > accounting_structure.sql

# Експорт тільки даних (без структури)
mysqldump -u root -p --no-create-info accounting > accounting_data.sql

# Експорт окремої таблиці
mysqldump -u root -p accounting Programs > programs_backup.sql
```

#### Спосіб 2: SELECT INTO OUTFILE

```sql
-- Експорт у CSV
SELECT * FROM Students
INTO OUTFILE '/tmp/students.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

#### Спосіб 3: phpMyAdmin

1. Виберіть базу даних `accounting`
2. Натисніть вкладку **"Експорт"**
3. Оберіть формат (SQL, CSV, JSON тощо)
4. Натисніть **"Виконати"**

---

### Q3: Як імпортувати дані?

**Відповідь:** Залежить від формату даних.

#### Спосіб 1: mysql (SQL файл)

```bash
# Імпорт SQL файлу
mysql -u root -p accounting < accounting_backup.sql

# З відображенням прогресу
pv accounting_backup.sql | mysql -u root -p accounting
```

#### Спосіб 2: SOURCE команда

```sql
USE accounting;
SOURCE /path/to/backup.sql;
```

#### Спосіб 3: LOAD DATA INFILE (CSV)

```sql
LOAD DATA INFILE '/tmp/students.csv'
INTO TABLE Students
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS; -- Пропустити заголовок
```

#### Спосіб 4: phpMyAdmin

1. Виберіть базу даних `accounting`
2. Натисніть вкладку **"Імпорт"**
3. Оберіть файл
4. Натисніть **"Виконати"**

---

### Q4: Як змінити тип фінансування для всіх студентів групи?

**Відповідь:** Використовуйте UPDATE з підзапитом або JOIN.

```sql
-- Спосіб 1: Через підзапит
UPDATE Students 
SET funding_type = 'contract' 
WHERE group_id = (SELECT id FROM `Groups` WHERE code = 'KB-25-1');

-- Спосіб 2: Прямий запит (якщо знаєте group_id)
UPDATE Students 
SET funding_type = 'contract' 
WHERE group_id = 1;

-- Спосіб 3: З перевіркою поточного статусу
UPDATE Students 
SET funding_type = 'contract' 
WHERE group_id = 1 AND funding_type = 'state';

-- Перевірка результату
SELECT s.name, s.funding_type, g.code
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
WHERE g.code = 'KB-25-1';
```

---

### Q5: Як знайти студентів певного віку?

**Відповідь:** Використовуйте функції дати для розрахунку віку.

```sql
-- Студенти віком 18 років
SELECT 
    id,
    name,
    birth_date,
    YEAR(CURDATE()) - YEAR(birth_date) AS age
FROM Students
WHERE YEAR(CURDATE()) - YEAR(birth_date) = 18;

-- Студенти від 17 до 20 років
SELECT 
    id,
    name,
    birth_date,
    YEAR(CURDATE()) - YEAR(birth_date) AS age
FROM Students
WHERE YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 17 AND 20;

-- Точний розрахунок віку (з урахуванням місяця і дня)
SELECT 
    id,
    name,
    birth_date,
    TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS exact_age
FROM Students
WHERE TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) = 18;
```

---

## Технічні питання

### Q6: Як додати нове поле в таблицю?

**Відповідь:** Використовуйте ALTER TABLE ADD COLUMN.

```sql
-- Додати поле в кінець таблиці
ALTER TABLE Students 
ADD COLUMN phone VARCHAR(20) DEFAULT NULL;

-- Додати поле після конкретного поля
ALTER TABLE Students 
ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER name;

-- Додати поле з обмеженнями
ALTER TABLE Students 
ADD COLUMN student_card VARCHAR(20) UNIQUE DEFAULT NULL;

-- Перевірка
DESCRIBE Students;
```

**Приклад:** Додати поле для електронної пошти

```sql
ALTER TABLE Students 
ADD COLUMN email VARCHAR(100) DEFAULT NULL,
ADD COLUMN phone VARCHAR(20) DEFAULT NULL;

-- Додати обмеження UNIQUE для email
ALTER TABLE Students 
ADD UNIQUE KEY unique_email (email);
```

---

### Q7: Як видалити всі дані без видалення таблиць?

**Відповідь:** Використовуйте TRUNCATE або DELETE.

#### TRUNCATE (швидше, скидає AUTO_INCREMENT)

```sql
-- Правильний порядок (через зовнішні ключі)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Addresses;
TRUNCATE TABLE Students;
TRUNCATE TABLE `Groups`;
TRUNCATE TABLE Programs;
SET FOREIGN_KEY_CHECKS = 1;
```

#### DELETE (повільніше, зберігає AUTO_INCREMENT)

```sql
-- Правильний порядок
DELETE FROM Addresses;
DELETE FROM Students;
DELETE FROM `Groups`;
DELETE FROM Programs;

-- Опціонально: скинути AUTO_INCREMENT
ALTER TABLE Programs AUTO_INCREMENT = 1;
ALTER TABLE `Groups` AUTO_INCREMENT = 1;
ALTER TABLE Students AUTO_INCREMENT = 1;
ALTER TABLE Addresses AUTO_INCREMENT = 1;
```

**Різниця між TRUNCATE та DELETE:**

| Характеристика | TRUNCATE | DELETE |
|----------------|----------|--------|
| Швидкість | Дуже швидко | Повільніше |
| AUTO_INCREMENT | Скидається | Зберігається |
| WHERE умова | Не підтримується | Підтримується |
| Відкат (ROLLBACK) | Неможливий | Можливий |
| Тригери | Не спрацьовують | Спрацьовують |

---

### Q8: Як переіменувати таблицю?

**Відповідь:** Використовуйте RENAME TABLE або ALTER TABLE.

```sql
-- Спосіб 1: RENAME TABLE
RENAME TABLE Programs TO EducationalPrograms;

-- Спосіб 2: ALTER TABLE
ALTER TABLE Programs RENAME TO EducationalPrograms;

-- Множинне переіменування
RENAME TABLE 
    Programs TO EducationalPrograms,
    `Groups` TO StudentGroups;

-- Повернути назву назад
RENAME TABLE EducationalPrograms TO Programs;
```

**Увага:** Переіменування таблиці не змінює імена зовнішніх ключів автоматично!

---

### Q9: Як створити резервну копію окремої таблиці?

**Відповідь:** Є кілька способів.

#### Спосіб 1: CREATE TABLE ... AS SELECT

```sql
-- Повна копія (структура + дані)
CREATE TABLE Students_backup AS SELECT * FROM Students;

-- Тільки структура
CREATE TABLE Students_backup LIKE Students;

-- Тільки певні дані
CREATE TABLE Students_budget AS 
SELECT * FROM Students WHERE funding_type = 'state';
```

#### Спосіб 2: mysqldump

```bash
# Експорт однієї таблиці
mysqldump -u root -p accounting Students > students_backup.sql

# Імпорт
mysql -u root -p accounting < students_backup.sql
```

#### Спосіб 3: INSERT INTO SELECT

```sql
-- Створити таблицю бекапу
CREATE TABLE Students_backup LIKE Students;

-- Скопіювати дані
INSERT INTO Students_backup SELECT * FROM Students;
```

---

### Q10: Як подивитися структуру таблиці?

**Відповідь:** Є кілька корисних команд.

```sql
-- Базовий опис полів
DESCRIBE Students;

-- Або скорочена форма
DESC Students;

-- Повна інформація про створення таблиці
SHOW CREATE TABLE Students;

-- Інформація про колонки
SHOW COLUMNS FROM Students;

-- Інформація про індекси
SHOW INDEX FROM Students;

-- Інформація про зовнішні ключі
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'accounting' 
    AND TABLE_NAME = 'Students'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## Запити та операції

### Q11: Як знайти студентів без адрес?

**Відповідь:** Використовуйте LEFT JOIN з перевіркою NULL.

```sql
SELECT 
    s.id,
    s.name,
    g.code AS group_code,
    CASE 
        WHEN a.id IS NULL THEN '❌ Немає адреси'
        ELSE '✅ Є адреса'
    END AS address_status
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
LEFT JOIN Addresses a ON s.id = a.student_id
WHERE a.id IS NULL
ORDER BY s.name;
```

---

### Q12: Як знайти найстаршого і наймолодшого студента?

**Відповідь:** Використовуйте MIN() та MAX().

```sql
-- Найстарший студент
SELECT 
    name,
    birth_date,
    YEAR(CURDATE()) - YEAR(birth_date) AS age
FROM Students
WHERE birth_date = (SELECT MIN(birth_date) FROM Students);

-- Наймолодший студент
SELECT 
    name,
    birth_date,
    YEAR(CURDATE()) - YEAR(birth_date) AS age
FROM Students
WHERE birth_date = (SELECT MAX(birth_date) FROM Students);

-- Обидва в одному запиті
(SELECT 'Найстарший' AS type, name, birth_date 
 FROM Students 
 ORDER BY birth_date ASC LIMIT 1)
UNION ALL
(SELECT 'Наймолодший', name, birth_date 
 FROM Students 
 ORDER BY birth_date DESC LIMIT 1);
```

---

### Q13: Як знайти групи з найбільшою кількістю студентів?

**Відповідь:** Використовуйте GROUP BY та ORDER BY.

```sql
-- Топ-3 групи за кількістю студентів
SELECT 
    g.code,
    p.title AS program,
    COUNT(s.id) AS students_count
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY g.id, g.code, p.title
ORDER BY students_count DESC
LIMIT 3;

-- Групи з максимальною кількістю
SELECT 
    g.code,
    COUNT(s.id) AS students_count
FROM `Groups` g
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY g.id, g.code
HAVING students_count = (
    SELECT MAX(cnt) FROM (
        SELECT COUNT(s2.id) AS cnt
        FROM `Groups` g2
        LEFT JOIN Students s2 ON g2.id = s2.group_id
        GROUP BY g2.id
    ) AS subquery
);
```

---

### Q14: Як перевести всіх студентів з однієї програми на іншу?

**Відповідь:** Потрібно змінити program_id у групах або перевести студентів у групи іншої програми.

```sql
-- Варіант 1: Перевести групи на іншу програму (якщо можливо)
UPDATE `Groups` 
SET program_id = 2 
WHERE program_id = 1;

-- Варіант 2: Перевести студентів у конкретну групу іншої програми
-- Спочатку знайдемо цільову групу
SELECT id, code FROM `Groups` WHERE program_id = 2 LIMIT 1;

-- Перевести студентів
UPDATE Students 
SET group_id = 2  -- ID групи з іншої програми
WHERE group_id IN (
    SELECT id FROM `Groups` WHERE program_id = 1
);
```

---

### Q15: Як створити звіт про розподіл студентів за містами?

**Відповідь:** Використовуйте GROUP BY з COUNT().

```sql
SELECT 
    a.city,
    COUNT(s.id) AS students_count,
    COUNT(CASE WHEN s.funding_type = 'state' THEN 1 END) AS budget_count,
    COUNT(CASE WHEN s.funding_type = 'contract' THEN 1 END) AS contract_count,
    GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') AS students_list
FROM Addresses a
JOIN Students s ON a.student_id = s.id
GROUP BY a.city
ORDER BY students_count DESC, a.city;
```

---

## Корисні запити

### Статистика по програмах

```sql
SELECT 
    p.id,
    p.title,
    COUNT(DISTINCT g.id) AS groups_count,
    COUNT(s.id) AS students_count,
    SUM(CASE WHEN s.funding_type = 'state' THEN 1 ELSE 0 END) AS budget_students,
    SUM(CASE WHEN s.funding_type = 'contract' THEN 1 ELSE 0 END) AS contract_students,
    ROUND(AVG(YEAR(CURDATE()) - YEAR(s.birth_date)), 1) AS average_age
FROM Programs p
LEFT JOIN `Groups` g ON p.id = g.program_id
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY p.id, p.title
ORDER BY students_count DESC;
```

**Очікуваний результат:**
```
+----+---------------------+--------------+----------------+-----------------+-------------------+-------------+
| id | title               | groups_count | students_count | budget_students | contract_students | average_age |
+----+---------------------+--------------+----------------+-----------------+-------------------+-------------+
|  1 | Кібербезпека        |            1 |              2 |               1 |                 1 |        18.1 |
|  2 | Інженерія ПЗ        |            1 |              2 |               1 |                 1 |        18.3 |
|  3 | Комп'ютерні науки   |            1 |              1 |               1 |                 0 |        17.6 |
+----+---------------------+--------------+----------------+-----------------+-------------------+-------------+
```

---

### Топ-5 найпопулярніших міст

```sql
SELECT 
    a.city,
    COUNT(s.id) AS students_count,
    ROUND(COUNT(s.id) * 100.0 / (SELECT COUNT(*) FROM Students WHERE id IN (SELECT student_id FROM Addresses)), 2) AS percentage
FROM Addresses a
JOIN Students s ON a.student_id = s.id
GROUP BY a.city
ORDER BY students_count DESC
LIMIT 5;
```

**Очікуваний результат:**
```
+-----------+----------------+------------+
| city      | students_count | percentage |
+-----------+----------------+------------+
| Київ      |              1 |      25.00 |
| Львів     |              1 |      25.00 |
| Суми      |              1 |      25.00 |
| Чернігів  |              1 |      25.00 |
+-----------+----------------+------------+
```

---

### Середній вік студентів по групах

```sql
SELECT 
    g.code,
    p.title AS program,
    COUNT(s.id) AS students_count,
    ROUND(AVG(YEAR(CURDATE()) - YEAR(s.birth_date)), 1) AS average_age,
    MIN(YEAR(CURDATE()) - YEAR(s.birth_date)) AS min_age,
    MAX(YEAR(CURDATE()) - YEAR(s.birth_date)) AS max_age
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY g.id, g.code, p.title
HAVING students_count > 0
ORDER BY average_age DESC;
```

**Очікуваний результат:**
```
+----------+---------------------+----------------+-------------+---------+---------+
| code     | program             | students_count | average_age | min_age | max_age |
+----------+---------------------+----------------+-------------+---------+---------+
| IPZ-25-1 | Інженерія ПЗ        |              2 |        18.3 |      17 |      18 |
| KB-25-1  | Кібербезпека        |              2 |        18.1 |      17 |      18 |
| CS-25-1  | Комп'ютерні науки   |              1 |        17.6 |      17 |      17 |
+----------+---------------------+----------------+-------------+---------+---------+
```

---

### Студенти народжені у певному році

```sql
-- Студенти народжені у 2007 році
SELECT 
    s.id,
    s.name,
    s.birth_date,
    g.code,
    p.title
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
WHERE YEAR(s.birth_date) = 2007
ORDER BY s.birth_date;
```

---

### Студенти з найдовшим іменем

```sql
SELECT 
    name,
    CHAR_LENGTH(name) AS name_length,
    g.code,
    p.title
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
ORDER BY name_length DESC
LIMIT 5;
```

---

### Порожні групи (без студентів)

```sql
SELECT 
    g.id,
    g.code,
    p.title AS program,
    COUNT(s.id) AS students_count,
    CASE 
        WHEN COUNT(s.id) = 0 THEN '⚠️ Порожня група'
        ELSE '✅ Є студенти'
    END AS status
FROM `Groups` g
JOIN Programs p ON g.program_id = p.id
LEFT JOIN Students s ON g.id = s.group_id
GROUP BY g.id, g.code, p.title
HAVING students_count = 0;
```

---

## 🔗 Корисні посилання

- [Головна сторінка](../README.md)
- [Швидкий старт](QUICK_START.md)
- [Структура БД](DATABASE_STRUCTURE.md)
- [CRUD операції](CRUD_OPERATIONS.md)
- [Типові сценарії](USE_CASES.md)
- [Обробка помилок](ERROR_HANDLING.md)

---

**Версія:** 1.0  
**Дата оновлення:** 2025-12-08

*Якщо у вас є інші питання, створіть Issue на GitHub!*
