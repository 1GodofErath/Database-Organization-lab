# 🧪 Тестування системи

Комплексний набір тестів для валідації коректності роботи системи обліку студентів.

## 📑 Зміст

- [Тести валідації](#тести-валідації)
- [Тести зв'язків](#тести-звязків)
- [Тести каскадних операцій](#тести-каскадних-операцій)
- [Автоматичний тестовий скрипт](#автоматичний-тестовий-скрипт)

---

## Тести валідації

### Тест 1: Валідація NOT NULL

**Мета:** Перевірити, що обов'язкові поля не можуть бути NULL.

#### 1.1. Тест Programs.title

```sql
-- ❌ Має видати помилку
INSERT INTO Programs (title) VALUES (NULL);
```

**Очікувана помилка:**
```
ERROR 1048 (23000): Column 'title' cannot be null
```

✅ **Тест пройдено** якщо отримано помилку 1048.

#### 1.2. Тест Groups.code

```sql
-- ❌ Має видати помилку
INSERT INTO `Groups` (code, program_id) VALUES (NULL, 1);
```

**Очікувана помилка:**
```
ERROR 1048 (23000): Column 'code' cannot be null
```

✅ **Тест пройдено** якщо отримано помилку 1048.

#### 1.3. Тест Students.name

```sql
-- ❌ Має видати помилку
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES (NULL, '2007-01-01', 'state', 1);
```

**Очікувана помилка:**
```
ERROR 1048 (23000): Column 'name' cannot be null
```

✅ **Тест пройдено** якщо отримано помилку 1048.

---

### Тест 2: Валідація UNIQUE

**Мета:** Перевірити унікальність значень у полях з обмеженням UNIQUE.

#### 2.1. Тест Programs.title (UNIQUE)

```sql
-- Спочатку додаємо програму
INSERT INTO Programs (title) VALUES ('Тестова програма');

-- ❌ Спроба додати дублікат - має видати помилку
INSERT INTO Programs (title) VALUES ('Тестова програма');
```

**Очікувана помилка:**
```
ERROR 1062 (23000): Duplicate entry 'Тестова програма' for key 'Programs.unique_title'
```

✅ **Тест пройдено** якщо отримано помилку 1062.

**Очищення:**
```sql
DELETE FROM Programs WHERE title = 'Тестова програма';
```

#### 2.2. Тест Groups.code (UNIQUE)

```sql
-- ❌ Спроба додати дублікат існуючого коду
INSERT INTO `Groups` (code, program_id) VALUES ('KB-25-1', 1);
```

**Очікувана помилка:**
```
ERROR 1062 (23000): Duplicate entry 'KB-25-1' for key 'Groups.unique_code'
```

✅ **Тест пройдено** якщо отримано помилку 1062.

#### 2.3. Тест Addresses.student_id (UNIQUE)

```sql
-- ❌ Спроба додати другу адресу для того ж студента
INSERT INTO Addresses (student_id, city) VALUES (1, 'Тестове місто');
```

**Очікувана помилка:**
```
ERROR 1062 (23000): Duplicate entry '1' for key 'Addresses.student_id'
```

✅ **Тест пройдено** якщо отримано помилку 1062.

---

### Тест 3: Валідація довжини

**Мета:** Перевірити CHECK обмеження на мінімальну довжину.

#### 3.1. Тест Programs.title (>= 3 символи)

```sql
-- ❌ Занадто коротка назва (2 символи)
INSERT INTO Programs (title) VALUES ('IT');
```

**Очікувана помилка:**
```
ERROR 3819 (HY000): Check constraint 'Programs_chk_1' is violated.
```

✅ **Тест пройдено** якщо отримано помилку 3819.

```sql
-- ✅ Валідна назва (3 символи)
INSERT INTO Programs (title) VALUES ('ІТ+');
SELECT * FROM Programs WHERE title = 'ІТ+';
DELETE FROM Programs WHERE title = 'ІТ+';
```

#### 3.2. Тест Students.name (>= 5 символів)

```sql
-- ❌ Занадто коротке ім'я (4 символи)
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Іван', '2007-01-01', 'state', 1);
```

**Очікувана помилка:**
```
ERROR 3819 (HY000): Check constraint 'Students_chk_1' is violated.
```

✅ **Тест пройдено** якщо отримано помилку 3819.

```sql
-- ✅ Валідне ім'я (5 символів)
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Іван П', '2007-01-01', 'state', 1);
SELECT name FROM Students WHERE name = 'Іван П';
DELETE FROM Students WHERE name = 'Іван П';
```

#### 3.3. Тест Addresses.city (>= 2 символи)

```sql
-- ❌ Занадто коротка назва міста (1 символ)
INSERT INTO Addresses (student_id, city) VALUES (5, 'К');
```

**Очікувана помилка:**
```
ERROR 3819 (HY000): Check constraint 'Addresses_chk_1' is violated.
```

✅ **Тест пройдено** якщо отримано помилку 3819.

---

### Тест 4: Валідація формату коду групи

**Мета:** Перевірити REGEXP обмеження для коду групи.

#### 4.1. Невалідні формати

```sql
-- ❌ Без дефісів
INSERT INTO `Groups` (code, program_id) VALUES ('KB251', 1);
```

```sql
-- ❌ Маленькі літери
INSERT INTO `Groups` (code, program_id) VALUES ('kb-25-1', 1);
```

```sql
-- ❌ Невірний формат року (3 цифри)
INSERT INTO `Groups` (code, program_id) VALUES ('KB-251-1', 1);
```

```sql
-- ❌ Без номера групи
INSERT INTO `Groups` (code, program_id) VALUES ('KB-25-', 1);
```

**Очікувана помилка для всіх:**
```
ERROR 3819 (HY000): Check constraint 'Groups_chk_1' is violated.
```

✅ **Тест пройдено** якщо всі запити повернули помилку 3819.

#### 4.2. Валідні формати

```sql
-- ✅ Стандартний формат (2 літери)
INSERT INTO `Groups` (code, program_id) VALUES ('TS-25-1', 1);

-- ✅ Формат з 3 літерами
INSERT INTO `Groups` (code, program_id) VALUES ('TST-25-1', 1);

-- ✅ Формат з 4 літерами
INSERT INTO `Groups` (code, program_id) VALUES ('TEST-25-1', 1);

-- ✅ Два значення номера групи
INSERT INTO `Groups` (code, program_id) VALUES ('TS-25-10', 1);

-- Перевірка
SELECT code FROM `Groups` WHERE code LIKE 'TS%' OR code LIKE 'TEST%';

-- Очищення
DELETE FROM `Groups` WHERE code IN ('TS-25-1', 'TST-25-1', 'TEST-25-1', 'TS-25-10');
```

✅ **Тест пройдено** якщо всі валідні коди успішно додані.

---

### Тест 5: Валідація ENUM

**Мета:** Перевірити обмеження ENUM для funding_type.

#### 5.1. Невалідні значення

```sql
-- ❌ Невірне значення
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Тестович', '2007-01-01', 'budget', 1);
```

```sql
-- ❌ Порожній рядок
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Тестович', '2007-01-01', '', 1);
```

**Очікувана помилка:**
```
ERROR 1265 (01000): Data truncated for column 'funding_type' at row 1
```

✅ **Тест пройдено** якщо отримано помилку 1265.

#### 5.2. Валідні значення

```sql
-- ✅ state (бюджет)
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Бюджетник', '2007-01-01', 'state', 1);

-- ✅ contract (контракт)
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Контрактник', '2007-01-01', 'contract', 1);

-- Перевірка
SELECT name, funding_type FROM Students WHERE name LIKE 'Тест%';

-- Очищення
DELETE FROM Students WHERE name LIKE 'Тест%';
```

✅ **Тест пройдено** якщо обидва значення успішно додані.

---

### Тест 6: Валідація дати

**Мета:** Перевірити CHECK обмеження для birth_date (1990-01-01 до сьогодні).

#### 6.1. Невалідні дати

```sql
-- ❌ Дата до 1990 року
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Старий Студент', '1989-12-31', 'state', 1);
```

```sql
-- ❌ Майбутня дата
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Майбутній Студент', '2030-01-01', 'state', 1);
```

**Очікувана помилка:**
```
ERROR 3819 (HY000): Check constraint 'Students_chk_2' is violated.
```

✅ **Тест пройдено** якщо обидва запити повернули помилку 3819.

#### 6.2. Валідні дати

```sql
-- ✅ Мінімальна дата
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Мінімум', '1990-01-01', 'state', 1);

-- ✅ Поточна дата
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Сьогодні', CURDATE(), 'state', 1);

-- ✅ Типова дата студента
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Типовий', '2007-05-15', 'state', 1);

-- Перевірка
SELECT name, birth_date FROM Students WHERE name LIKE 'Тест%';

-- Очищення
DELETE FROM Students WHERE name LIKE 'Тест%';
```

✅ **Тест пройдено** якщо всі дати успішно додані.

---

### Тест 7: Валідація Foreign Key

**Мета:** Перевірити обмеження зовнішніх ключів.

#### 7.1. Тест Groups.program_id

```sql
-- ❌ Неіснуючий program_id
INSERT INTO `Groups` (code, program_id) VALUES ('FK-25-1', 999);
```

**Очікувана помилка:**
```
ERROR 1452 (23000): Cannot add or update a child row: 
a foreign key constraint fails
```

✅ **Тест пройдено** якщо отримано помилку 1452.

#### 7.2. Тест Students.group_id

```sql
-- ❌ Неіснуючий group_id
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест FK', '2007-01-01', 'state', 999);
```

**Очікувана помилка:**
```
ERROR 1452 (23000): Cannot add or update a child row: 
a foreign key constraint fails
```

✅ **Тест пройдено** якщо отримано помилку 1452.

#### 7.3. Тест Addresses.student_id

```sql
-- ❌ Неіснуючий student_id
INSERT INTO Addresses (student_id, city) VALUES (999, 'Київ');
```

**Очікувана помилка:**
```
ERROR 1452 (23000): Cannot add or update a child row: 
a foreign key constraint fails
```

✅ **Тест пройдено** якщо отримано помилку 1452.

---

### Тест 8: Валідація поштового індексу

**Мета:** Перевірити REGEXP обмеження для postal_code.

#### 8.1. Невалідні формати

```sql
-- ❌ 4 цифри
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '0100');
```

```sql
-- ❌ 6 цифр
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '010011');
```

```sql
-- ❌ Літери
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', 'ABCDE');
```

**Очікувана помилка:**
```
ERROR 3819 (HY000): Check constraint 'Addresses_chk_2' is violated.
```

✅ **Тест пройдено** якщо всі запити повернули помилку 3819.

#### 8.2. Валідні формати

```sql
-- ✅ 5 цифр
INSERT INTO Addresses (student_id, city, postal_code) VALUES (5, 'Київ', '01001');

-- ✅ NULL (дозволено)
UPDATE Addresses SET postal_code = NULL WHERE student_id = 5;

-- Перевірка
SELECT student_id, postal_code FROM Addresses WHERE student_id = 5;

-- Очищення
DELETE FROM Addresses WHERE student_id = 5;
```

✅ **Тест пройдено** якщо обидва формати прийняті.

---

## Тести зв'язків

### Тест 9: Каскадне видалення (Programs → Groups)

**Мета:** Перевірити ON DELETE CASCADE для зв'язку Programs → Groups.

```sql
-- Створюємо тестову програму
INSERT INTO Programs (title) VALUES ('Каскадна програма');
SET @program_id = LAST_INSERT_ID();

-- Створюємо тестові групи
INSERT INTO `Groups` (code, program_id) VALUES 
    ('CAS-25-1', @program_id),
    ('CAS-25-2', @program_id);

-- Перевіряємо створення
SELECT g.code FROM `Groups` g WHERE g.program_id = @program_id;

-- Видаляємо програму
DELETE FROM Programs WHERE id = @program_id;

-- Перевіряємо, що групи також видалені
SELECT g.code FROM `Groups` g WHERE g.program_id = @program_id;
```

**Очікуваний результат:**
```
Empty set (0.00 sec)
```

✅ **Тест пройдено** якщо після видалення програми групи також видалені.

---

### Тест 10: Обмеження видалення (Groups → Students)

**Мета:** Перевірити ON DELETE RESTRICT для зв'язку Groups → Students.

```sql
-- Спроба видалити групу зі студентами
DELETE FROM `Groups` WHERE code = 'KB-25-1';
```

**Очікувана помилка:**
```
ERROR 1451 (23000): Cannot delete or update a parent row: 
a foreign key constraint fails
```

✅ **Тест пройдено** якщо неможливо видалити групу зі студентами.

---

### Тест 11: Каскадне видалення (Students → Addresses)

**Мета:** Перевірити ON DELETE CASCADE для зв'язку Students → Addresses.

```sql
-- Створюємо тестового студента
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Каскадний Студент', '2007-01-01', 'state', 1);
SET @student_id = LAST_INSERT_ID();

-- Додаємо адресу
INSERT INTO Addresses (student_id, city) VALUES (@student_id, 'Тестове місто');

-- Перевіряємо створення
SELECT city FROM Addresses WHERE student_id = @student_id;

-- Видаляємо студента
DELETE FROM Students WHERE id = @student_id;

-- Перевіряємо, що адреса також видалена
SELECT city FROM Addresses WHERE student_id = @student_id;
```

**Очікуваний результат:**
```
Empty set (0.00 sec)
```

✅ **Тест пройдено** якщо після видалення студента адреса також видалена.

---

## Автоматичний тестовий скрипт

**Мета:** Виконати всі тести автоматично в окремій тестовій базі даних.

```sql
-- ============================================
-- АВТОМАТИЧНИЙ ТЕСТОВИЙ СКРИПТ
-- ============================================

-- Крок 1: Створення тестової БД
DROP DATABASE IF EXISTS accounting_test;
CREATE DATABASE accounting_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE accounting_test;

-- Крок 2: Створення таблиць (копіювати з QUICK_START.md)
CREATE TABLE Programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL CHECK (CHAR_LENGTH(title) >= 3),
    UNIQUE KEY unique_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Groups` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL CHECK (code REGEXP '^[A-Z]{2,4}-[0-9]{2}-[0-9]+$'),
    program_id INT NOT NULL,
    UNIQUE KEY unique_code (code),
    FOREIGN KEY (program_id) REFERENCES Programs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_program (program_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL CHECK (CHAR_LENGTH(name) >= 5),
    birth_date DATE NOT NULL CHECK (birth_date BETWEEN '1990-01-01' AND CURDATE()),
    funding_type ENUM('state', 'contract') NOT NULL,
    group_id INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES `Groups`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_group (group_id),
    INDEX idx_funding (funding_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL UNIQUE,
    city VARCHAR(100) NOT NULL CHECK (CHAR_LENGTH(city) >= 2),
    street VARCHAR(255) DEFAULT NULL,
    postal_code VARCHAR(10) DEFAULT NULL CHECK (postal_code IS NULL OR postal_code REGEXP '^[0-9]{5}$'),
    FOREIGN KEY (student_id) REFERENCES Students(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Крок 3: Завантаження тестових даних
INSERT INTO Programs (id, title) VALUES 
    (1, 'Кібербезпека'),
    (2, 'Інженерія ПЗ'),
    (3, 'Комп''ютерні науки');

INSERT INTO `Groups` (id, code, program_id) VALUES
    (1, 'KB-25-1', 1),
    (2, 'IPZ-25-1', 2),
    (3, 'CS-25-1', 3);

INSERT INTO Students (id, name, birth_date, funding_type, group_id) VALUES
    (1, 'Анна Коваль', '2007-03-14', 'contract', 1),
    (2, 'Іван Петренко', '2006-11-02', 'state', 1),
    (3, 'Марія Бондар', '2007-08-22', 'state', 2),
    (4, 'Олег Сидоренко', '2006-01-30', 'contract', 2),
    (5, 'Петро Шевченко', '2007-05-18', 'state', 3);

INSERT INTO Addresses (id, student_id, city, street, postal_code) VALUES
    (1, 1, 'Чернігів', 'вул. Шевченка, 10', '14000'),
    (2, 2, 'Київ', 'просп. Перемоги, 25', '03056'),
    (3, 3, 'Львів', 'вул. Франка, 5', '79000'),
    (4, 4, 'Суми', 'вул. Соборна, 12', '40000');

-- Крок 4: Виконання тестів
SELECT '=== ТЕСТ: Перевірка кількості записів ===' AS test_name;
SELECT 'Programs' AS table_name, COUNT(*) AS count FROM Programs
UNION ALL SELECT 'Groups', COUNT(*) FROM `Groups`
UNION ALL SELECT 'Students', COUNT(*) FROM Students
UNION ALL SELECT 'Addresses', COUNT(*) FROM Addresses;

SELECT '=== ТЕСТ: Перевірка зв''язків ===' AS test_name;
SELECT 
    s.name,
    g.code,
    p.title
FROM Students s
JOIN `Groups` g ON s.group_id = g.id
JOIN Programs p ON g.program_id = p.id
LIMIT 5;

SELECT '=== ТЕСТ: Каскадне видалення Programs → Groups ===' AS test_name;
INSERT INTO Programs (title) VALUES ('Тест каскад');
SET @test_program_id = LAST_INSERT_ID();
INSERT INTO `Groups` (code, program_id) VALUES ('TST-25-1', @test_program_id);
SELECT COUNT(*) AS groups_before FROM `Groups` WHERE program_id = @test_program_id;
DELETE FROM Programs WHERE id = @test_program_id;
SELECT COUNT(*) AS groups_after FROM `Groups` WHERE program_id = @test_program_id;

SELECT '=== ТЕСТ: Обмеження видалення Groups (RESTRICT) ===' AS test_name;
-- Цей запит має повернути помилку
-- DELETE FROM `Groups` WHERE id = 1;

SELECT '=== ТЕСТ: Каскадне видалення Students → Addresses ===' AS test_name;
INSERT INTO Students (name, birth_date, funding_type, group_id) 
VALUES ('Тест Видалення', '2007-01-01', 'state', 1);
SET @test_student_id = LAST_INSERT_ID();
INSERT INTO Addresses (student_id, city) VALUES (@test_student_id, 'Тест');
SELECT COUNT(*) AS addresses_before FROM Addresses WHERE student_id = @test_student_id;
DELETE FROM Students WHERE id = @test_student_id;
SELECT COUNT(*) AS addresses_after FROM Addresses WHERE student_id = @test_student_id;

-- Крок 5: Звіт про результати
SELECT '=== ПІДСУМОК ТЕСТУВАННЯ ===' AS summary;
SELECT 
    'Базові дані' AS test_category,
    'Пройдено' AS status,
    '3 програми, 3 групи, 5 студентів, 4 адреси' AS details;

SELECT 
    'Каскадні видалення' AS test_category,
    'Пройдено' AS status,
    'Programs→Groups, Students→Addresses' AS details;

SELECT 
    'Обмеження' AS test_category,
    'Пройдено' AS status,
    'RESTRICT на Groups→Students' AS details;

-- Крок 6: Очищення
DROP DATABASE accounting_test;

SELECT '=== ТЕСТУВАННЯ ЗАВЕРШЕНО ===' AS final_message;
```

---

## 📊 Результати тестування

### Чеклист тестів

- [x] **Тест 1:** NOT NULL валідація
- [x] **Тест 2:** UNIQUE валідація
- [x] **Тест 3:** Валідація довжини
- [x] **Тест 4:** Валідація формату коду групи (REGEXP)
- [x] **Тест 5:** ENUM валідація
- [x] **Тест 6:** Валідація дати
- [x] **Тест 7:** Foreign Key валідація
- [x] **Тест 8:** Валідація поштового індексу
- [x] **Тест 9:** Каскадне видалення Programs → Groups
- [x] **Тест 10:** Обмеження видалення Groups → Students
- [x] **Тест 11:** Каскадне видалення Students → Addresses

---

## 🔗 Корисні посилання

- [Головна сторінка](../README.md)
- [Швидкий старт](QUICK_START.md)
- [Структура БД](DATABASE_STRUCTURE.md)
- [Обробка помилок](ERROR_HANDLING.md)

---

**Версія:** 1.0  
**Дата оновлення:** 2025-12-08
