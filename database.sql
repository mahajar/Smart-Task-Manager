CREATE DATABASE smarttaskdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smarttaskdb;

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  due_date DATE DEFAULT NULL,
  importance TINYINT DEFAULT 1,
  priority_score FLOAT DEFAULT 0,
  completed TINYINT(1) DEFAULT 0
);
