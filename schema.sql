-- =====================================================
-- Online Examination Infrastructure System - Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS online_exam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_exam;

-- Users (Teachers & Students)
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(200) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('teacher','student') NOT NULL DEFAULT 'student',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Exam Rooms created by teachers
CREATE TABLE IF NOT EXISTS exam_rooms (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    subject     VARCHAR(150) NOT NULL,
    duration    INT NOT NULL COMMENT 'Duration in minutes',
    room_code   VARCHAR(10) NOT NULL UNIQUE,
    created_by  INT NOT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Questions linked to exam rooms
CREATE TABLE IF NOT EXISTS questions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    exam_room_id    INT NOT NULL,
    question_text   TEXT NOT NULL,
    question_type   ENUM('mcq','descriptive') NOT NULL DEFAULT 'mcq',
    marks           INT NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- MCQ Options linked to questions
CREATE TABLE IF NOT EXISTS options (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    question_id     INT NOT NULL,
    option_text     VARCHAR(500) NOT NULL,
    is_correct      TINYINT(1) DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Exam attempts by students
CREATE TABLE IF NOT EXISTS exam_attempts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    exam_room_id    INT NOT NULL,
    student_id      INT NOT NULL,
    score           DECIMAL(8,2) DEFAULT NULL,
    total_marks     INT DEFAULT 0,
    started_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at    TIMESTAMP NULL DEFAULT NULL,
    status          ENUM('ongoing','submitted') DEFAULT 'ongoing',
    UNIQUE KEY unique_attempt (exam_room_id, student_id),
    FOREIGN KEY (exam_room_id) REFERENCES exam_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)   REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Student answers per attempt
CREATE TABLE IF NOT EXISTS answers (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id          INT NOT NULL,
    question_id         INT NOT NULL,
    selected_option_id  INT DEFAULT NULL,
    descriptive_answer  TEXT DEFAULT NULL,
    is_correct          TINYINT(1) DEFAULT NULL,
    FOREIGN KEY (attempt_id)          REFERENCES exam_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id)         REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id)  REFERENCES options(id) ON DELETE SET NULL
) ENGINE=InnoDB;
