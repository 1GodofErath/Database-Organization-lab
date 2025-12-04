USE accounting;

CREATE TABLE IF NOT EXISTS Users (
                                     UserID INT AUTO_INCREMENT PRIMARY KEY,
                                     Username VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Role ENUM('admin', 'user') DEFAULT 'user',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (Username),
    INDEX idx_email (Email)
    );

CREATE TABLE IF NOT EXISTS Categories (
                                          CategoryID INT AUTO_INCREMENT PRIMARY KEY,
                                          CategoryName VARCHAR(100) NOT NULL UNIQUE,
    CategoryType ENUM('Income', 'Expense') NOT NULL,
    Description TEXT,
    INDEX idx_type (CategoryType)
    ) ;

CREATE TABLE IF NOT EXISTS Transactions (
                                            TransactionID INT AUTO_INCREMENT PRIMARY KEY,
                                            UserID INT NOT NULL,
                                            CategoryID INT NOT NULL,
                                            Amount DECIMAL(12, 2) NOT NULL,
    TransactionDate DATE NOT NULL,
    Description TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID) ON DELETE CASCADE,
    INDEX idx_user_date (UserID, TransactionDate),
    INDEX idx_category (CategoryID),
    INDEX idx_date (TransactionDate)
    );
CREATE DATABASE IF NOT EXISTS accounting;
USE accounting;

-- Таблиця користувачів
DROP TABLE IF EXISTS users;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(100) NOT NULL,
                       email VARCHAR(100) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

REVOKE ALL PRIVILEGES ON *.* FROM 'AdminUser'@'%';
REVOKE GRANT OPTION ON *.* FROM 'AdminUser'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, FILE, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON *.* TO 'AdminUser'@'%';
ALTER USER 'AdminUser'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;

