-- =========================================================
-- Exam Hall Allocation System - Database Script
-- Import this whole file in phpMyAdmin (XAMPP) or via:
--   mysql -u root -p < database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS exam_hall_allocation
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE exam_hall_allocation;

-- ---------------------------------------------------------
-- Table: admins
-- ---------------------------------------------------------
CREATE TABLE admins (
  admin_id   INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  full_name  VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: students
-- ---------------------------------------------------------
CREATE TABLE students (
  student_id  INT AUTO_INCREMENT PRIMARY KEY,
  register_no VARCHAR(30)  NOT NULL UNIQUE,
  name        VARCHAR(100) NOT NULL,
  department  VARCHAR(50)  NOT NULL,
  year        VARCHAR(10)  NOT NULL,
  section     VARCHAR(10)  NOT NULL,
  password    VARCHAR(255) NOT NULL,   -- default password = register_no (hashed)
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: exams
-- ---------------------------------------------------------
CREATE TABLE exams (
  exam_id      INT AUTO_INCREMENT PRIMARY KEY,
  subject_code VARCHAR(20)  NOT NULL,
  subject_name VARCHAR(100) NOT NULL,
  exam_date    DATE NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: halls
-- ---------------------------------------------------------
CREATE TABLE halls (
  hall_id     INT AUTO_INCREMENT PRIMARY KEY,
  hall_number VARCHAR(20) NOT NULL UNIQUE,
  building    VARCHAR(50) NOT NULL,
  floor       VARCHAR(10) NOT NULL,
  capacity    INT NOT NULL CHECK (capacity > 0),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: exam_registrations
-- (which students are registered / appearing for which exam)
-- ---------------------------------------------------------
CREATE TABLE exam_registrations (
  reg_id     INT AUTO_INCREMENT PRIMARY KEY,
  exam_id    INT NOT NULL,
  student_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)       ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  UNIQUE KEY uniq_exam_student (exam_id, student_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: allocations
-- (final hall/seat allocation produced by the algorithm)
-- ---------------------------------------------------------
CREATE TABLE allocations (
  allocation_id INT AUTO_INCREMENT PRIMARY KEY,
  exam_id       INT NOT NULL,
  student_id    INT NOT NULL,
  hall_id       INT NOT NULL,
  seat_no       VARCHAR(10) NOT NULL,
  allocated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (exam_id)    REFERENCES exams(exam_id)       ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (hall_id)    REFERENCES halls(hall_id)        ON DELETE CASCADE,
  UNIQUE KEY uniq_exam_student_alloc (exam_id, student_id)  -- a student can only be allocated once per exam
) ENGINE=InnoDB;

-- =========================================================
-- SAMPLE DATA
-- =========================================================

-- Admin login -> username: admin | password: admin123
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2b$12$yD5v4I8UpCHvyhf1igOkqusApVjCGoznrt1CVmygXoD/ArpEBNdGO', 'System Administrator');

-- Students -> each student's default password is their own Register Number
INSERT INTO students (register_no, name, department, year, section, password) VALUES
('CS2021001', 'Aarav Kumar',     'CSE', '3', 'A', '$2b$12$sxrjLP63/I01oh295MJrHOj2o1miGuwV7Ta2ojh7/TFwcVYBpJ.DK'),
('CS2021002', 'Bhavya Sharma',   'CSE', '3', 'A', '$2b$12$AGWM6kkcumGulqaKgnWT3.Oet50q.j3txPSq45BK1L4XeT4CueeOC'),
('CS2021003', 'Chetan Rao',      'CSE', '3', 'B', '$2b$12$/D11rAJTN/5jXN5pd3rM3eVHJleXPSUtPO/HzBQIYmvto7Bp3hidy'),
('CS2021004', 'Divya Patel',     'CSE', '3', 'B', '$2b$12$SOhFZLav/LRcWXa2N.BXVu7GTvhjv9kxC1NgFDNFjl487cNmBZem6'),
('CS2021005', 'Esha Nair',       'CSE', '3', 'A', '$2b$12$/ynr.z7cipOmdyKFu8dlKuCzLPCKGmjnXs7OuMX/W14n9dh9DVPxm'),
('IT2021001', 'Farhan Ali',      'IT',  '3', 'A', '$2b$12$gH4Efshhh47i4.FCpOpFg.6yhEYOCklSM/QlK9z7FknSWwOmBb89i'),
('IT2021002', 'Gayatri Iyer',    'IT',  '3', 'A', '$2b$12$Uqff3ojrIMrEPrYUtM68IOFtabqGhnDCGQBKBn5cBWQSfcj/oSALO'),
('EC2021001', 'Harish Menon',    'ECE', '2', 'A', '$2b$12$0ge2EkIjdyQ6sfJAkPWCf.FwkPvA58jX0.Fstk8Yyi5tYjFTy0qhG'),
('EC2021002', 'Isha Verma',      'ECE', '2', 'A', '$2b$12$.OxW0gAx1EL0DfPQcDtx2uk6CisX6KTIgB32glUW.2cvBcyQg3y5i'),
('ME2021001', 'Jatin Chawla',    'MECH','2', 'A', '$2b$12$ytemghFh0grqQWIGRgFbGO3HgrNt.zxi.LPN2UqCpQ18H/NG2NrpG');

-- Exams
INSERT INTO exams (subject_code, subject_name, exam_date, start_time, end_time) VALUES
('CS301', 'Database Management Systems', '2026-10-05', '09:30:00', '12:30:00'),
('CS302', 'Computer Networks',           '2026-10-07', '09:30:00', '12:30:00'),
('IT301', 'Operating Systems',           '2026-10-05', '09:30:00', '12:30:00');

-- Halls
INSERT INTO halls (hall_number, building, floor, capacity) VALUES
('H101', 'Main Block',   'Ground', 4),
('H102', 'Main Block',   'Ground', 4),
('H201', 'Science Block','First',  3);

-- Register all 10 sample students for exam_id 1 (CS301) to demo the allocation algorithm
INSERT INTO exam_registrations (exam_id, student_id)
SELECT 1, student_id FROM students;
