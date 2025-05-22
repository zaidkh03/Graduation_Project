-- =================================
-- Madrasati Data Base Schema Design
-- =================================

-- IMPORTANT: Name the DATABASE in the phpMyAdmin as 'madrasati'

-- 1. ADMINS
CREATE TABLE admins (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(30) UNIQUE NOT NULL,
  phone VARCHAR(30) UNIQUE NOT NULL
);

-- 2. SCHOOL
CREATE TABLE school (
  id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  location VARCHAR(30) NOT NULL,
  admin_id BIGINT(20) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(100) NULL,
  website VARCHAR(255) NULL,
  FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- 3. SCHOOL YEAR
CREATE TABLE school_year (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(9) NOT NULL UNIQUE
);

-- 4. SUBJECTS
CREATE TABLE subjects (
  id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE,
  description VARCHAR(100) NULL,
  pdf_path VARCHAR(255) NULL
);

-- 5. TEACHERS
CREATE TABLE teachers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  subject_id BIGINT,
  email VARCHAR(30) UNIQUE NOT NULL,
  phone VARCHAR(30) UNIQUE NOT NULL,
  FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- 6. CLASS
CREATE TABLE class (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT NOT NULL,
  school_year_id BIGINT NOT NULL,
  grade INT NOT NULL,
  section VARCHAR(10) NOT NULL,
  capacity INT NOT NULL,
  mentor_teacher_id BIGINT,
  students_json JSON,
  subject_teacher_map JSON,
  grading_status_json JSON,
  archived TINYINT(1) DEFAULT 0,
  FOREIGN KEY (school_id) REFERENCES school(id),
  FOREIGN KEY (school_year_id) REFERENCES school_year(id),
  FOREIGN KEY (mentor_teacher_id) REFERENCES teachers(id)
);

-- 7. TEACHER ASSIGNMENT
CREATE TABLE teacher_subject_class (
  teacher_id BIGINT,
  subject_id BIGINT,
  class_id BIGINT,
  PRIMARY KEY (teacher_id, subject_id, class_id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (class_id) REFERENCES class(id)
);

-- 8. PARENTS
CREATE TABLE parents (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(30) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL UNIQUE
);

-- 9. STUDENTS
CREATE TABLE students (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  birth_date DATE NOT NULL,
  gender ENUM('Male', 'Female') NOT NULL,
  address VARCHAR(100),
  current_grade INT,
  status ENUM('Active', 'Inactive') DEFAULT 'Active',
  parent_id BIGINT NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES parents(id)
);

-- 10. ACADEMIC RECORD
CREATE TABLE academic_record (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT NOT NULL,
  class_id BIGINT NOT NULL,
  school_year_id BIGINT NOT NULL,
  marks_json JSON,
  attendance_json JSON,
  attendance_response_json JSON DEFAULT '{}',
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (class_id) REFERENCES class(id),
  FOREIGN KEY (school_year_id) REFERENCES school_year(id)
);

-- 11. NOTIFICATIONS
CREATE TABLE notifications (
  id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT(20) NOT NULL,
  user_id JSON NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  send_to INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  read_by JSON NULL DEFAULT NULL,
  archived TINYINT(1) NOT NULL DEFAULT 0
);

-- 12. USERS (Login Management)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student','teacher','parent') NOT NULL,
  related_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
