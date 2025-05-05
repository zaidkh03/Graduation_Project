-- ===========================
-- ✅ FINAL DB SCHEMA (CLEAN + JSON INTEGRATED)
-- Based on full project use-case (admin, teacher, parent, student)
-- ===========================

-- 1. ADMINS
CREATE TABLE admin (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(30) UNIQUE NOT NULL,
  phone VARCHAR(30) UNIQUE NOT NULL,
  admin_dashboard_data JSON -- ✅ admin dashboard analytics
);

-- 2. SCHOOL
CREATE TABLE school (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  location VARCHAR(30) NOT NULL,
  admin_id BIGINT,
  FOREIGN KEY (admin_id) REFERENCES admin(id)
);

-- 3. SCHOOL YEAR
CREATE TABLE school_year (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(9) NOT NULL UNIQUE
);

-- 4. SUBJECTS
CREATE TABLE subjects (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE,
  description VARCHAR(100),
  book_name VARCHAR(30)
);

-- 5. TEACHERS
CREATE TABLE teachers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  subject_id BIGINT, -- ✅ Main subject specialization
  email VARCHAR(30) UNIQUE NOT NULL,
  phone VARCHAR(30) UNIQUE NOT NULL,
  teacher_dashboard_data JSON, -- ✅ Teacher dashboard and quick view data
  FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- 7. CLASS
CREATE TABLE class (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT NOT NULL,
  school_year_id BIGINT NOT NULL,
  grade INT NOT NULL,
  section VARCHAR(10) NOT NULL,
  capacity INT NOT NULL,
  mentor_teacher_id BIGINT,
  students_json JSON, -- ✅ list of assigned students
  subject_teacher_map JSON, -- ✅ links subject names or IDs to teacher IDs
  FOREIGN KEY (school_id) REFERENCES school(id),
  FOREIGN KEY (school_year_id) REFERENCES school_year(id),
  FOREIGN KEY (mentor_teacher_id) REFERENCES teachers(id)
);

-- 6. TEACHER ASSIGNMENT
CREATE TABLE teacher_subject_class (
  teacher_id BIGINT,
  subject_id BIGINT,
  class_id BIGINT,
  PRIMARY KEY (teacher_id, subject_id, class_id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (class_id) REFERENCES class(id)
);

-- 9. PARENTS
CREATE TABLE parents (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(30) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL UNIQUE
);

-- 8. STUDENTS
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
  student_dashboard_data JSON, -- ✅ profile/dashboard data cache
  FOREIGN KEY (parent_id) REFERENCES parents(id)
);

-- 10. ACADEMIC RECORD
CREATE TABLE academic_record (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT NOT NULL,
  class_id BIGINT NOT NULL,
  school_year_id BIGINT NOT NULL,
  marks_json JSON,        -- ✅ subject -> semester -> marks
  attendance_json JSON,   -- ✅ subject -> dates
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (class_id) REFERENCES class(id),
  FOREIGN KEY (school_year_id) REFERENCES school_year(id)
);

-- 11. ATTENDANCE AGREEMENT (for parent approval)
CREATE TABLE agreement (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  academic_record_id BIGINT,
  subject_id BIGINT,
  date DATE,
  parent_id BIGINT,
  reason VARCHAR(100),
  status ENUM('Approved', 'Rejected') DEFAULT 'Approved',
  FOREIGN KEY (academic_record_id) REFERENCES academic_record(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (parent_id) REFERENCES parents(id)
);

-- 12. NOTIFICATIONS
CREATE TABLE notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  user_role ENUM('Admin','Teacher','Student','Parent'),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 13. USERS (Login Management)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  national_id VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student','teacher','parent') NOT NULL,
  related_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 14. ANALYTICS CACHE
CREATE TABLE school_analytics_json (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  school_year_id BIGINT,
  report_type VARCHAR(30),
  data JSON,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
