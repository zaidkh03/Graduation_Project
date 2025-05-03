-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 28, 2025 at 02:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_record`
--

CREATE TABLE `academic_record` (
  `id` bigint(20) NOT NULL,
  `student_id` bigint(20) DEFAULT NULL,
  `class_id` bigint(20) DEFAULT NULL,
  `school_year_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `national_id`, `email`, `phone`) VALUES
(2, 'admin', '2000000000', 'admin@gmail.com', '0790000000');

-- --------------------------------------------------------

--
-- Table structure for table `agreement`
--

CREATE TABLE `agreement` (
  `id` bigint(20) NOT NULL,
  `attendance_id` bigint(20) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `reason` varchar(30) NOT NULL,
  `status` enum('Approved','Rejected') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) NOT NULL,
  `academic_record_id` bigint(20) DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `id` bigint(20) NOT NULL,
  `school_id` bigint(20) NOT NULL,
  `school_year_id` bigint(20) DEFAULT NULL,
  `grade` int(11) NOT NULL DEFAULT 1,
  `section` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 20,
  `mentor_teacher_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_components`
--

CREATE TABLE `grade_components` (
  `id` bigint(20) NOT NULL,
  `academic_record_id` bigint(20) DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `grade_type` enum('first_exam','second_exam','participation_mark','final_exam') DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT 100.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_summary`
--

CREATE TABLE `grade_summary` (
  `id` bigint(20) NOT NULL,
  `academic_record_id` bigint(20) DEFAULT NULL,
  `subject_id` bigint(20) DEFAULT NULL,
  `first_semester_total` decimal(5,2) DEFAULT NULL,
  `second_semester_total` decimal(5,2) DEFAULT NULL,
  `the_year_total` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_role` enum('Admin','Teacher','Student','Parent') DEFAULT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `name`, `national_id`, `email`, `phone`) VALUES
(2, 'parent', '2000000002', 'parent@gmail.com', '0790000002');

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE `school` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `location` varchar(30) NOT NULL,
  `admin_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `id` bigint(20) NOT NULL,
  `year` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `address` varchar(100) NOT NULL,
  `current_grade` int(11) NOT NULL DEFAULT 1,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `national_id`, `birth_date`, `gender`, `address`, `current_grade`, `status`, `parent_id`) VALUES
(2, 'student', '2000000001', '2003-09-16', 'Male', 'zarqa', 1, 'Active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_parent`
--

CREATE TABLE `student_parent` (
  `student_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` varchar(100) NOT NULL,
  `book_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `book_name`) VALUES
(1, 'Arabiccc', 'bluh bluh bluh', 'arabic10111');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `subject_id` bigint(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `national_id`, `subject_id`, `email`, `phone`) VALUES
(3, 'teacher', '2000000003', 1, 'teacher@gmail.com', '0790000243567'),
(5, 'test', '5555555555', 1, 'hbsobu@yscgvdh', '4898489646');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_class`
--

CREATE TABLE `teacher_subject_class` (
  `teacher_id` bigint(20) NOT NULL,
  `subject_id` bigint(20) NOT NULL,
  `class_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','teacher','parent') NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `national_id`, `password`, `role`, `related_id`, `created_at`, `updated_at`) VALUES
(5, '2000000000', '$2y$10$vFT6be8f8q8p3Ape7c.bledf90a3tDgTBmThZOiiV/smZMcca17fC', 'admin', 2, '2025-04-21 07:30:32', '2025-04-21 07:30:32'),
(6, '2000000001', '$2y$10$c4L/t99fqda2aUasBhIRgOeHt7SJ1NBKyb5MiU6tdcwA.2e/1vbgK', 'student', 2, '2025-04-21 07:57:33', '2025-04-21 07:57:33'),
(7, '2000000002', '$2y$10$WCXvE2v8kc..mupl7jbE0.M64JLZYgqSgI57Nac1TGjiLSjgoOdqS', 'parent', 2, '2025-04-21 07:58:18', '2025-04-21 07:58:18'),
(8, '2000000003', '$2y$10$2YRODea9czGpiKQZTv7w.uhWK185juk193CWUXxRa7CNSD114WyMC', 'teacher', 3, '2025-04-21 07:59:17', '2025-04-21 07:59:17'),
(13, '1111111113', '$2y$10$TOfrf5bPYHmTfojS5KcssuNxwz6n14dxzacwx1VARPpb7vH6UQhO2', 'teacher', 6, '2025-04-28 10:14:01', '2025-04-28 10:14:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_record`
--
ALTER TABLE `academic_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_academic_record_student` (`student_id`),
  ADD KEY `idx_academic_record_class` (`class_id`),
  ADD KEY `idx_academic_record_year` (`school_year_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_academic_record` (`academic_record_id`),
  ADD KEY `idx_attendance_subject` (`subject_id`),
  ADD KEY `idx_attendance_date` (`date`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_school` (`school_id`),
  ADD KEY `idx_class_school_year` (`school_year_id`),
  ADD KEY `idx_class_mentor_teacher` (`mentor_teacher_id`);

--
-- Indexes for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_record_id` (`academic_record_id`,`subject_id`,`semester`,`grade_type`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_grade_components_record` (`academic_record_id`);

--
-- Indexes for table `grade_summary`
--
ALTER TABLE `grade_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_record_id` (`academic_record_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_grade_summary_record` (`academic_record_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_role` (`user_role`),
  ADD KEY `idx_notifications_user_id` (`user_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD KEY `idx_student_national_id` (`national_id`);

--
-- Indexes for table `student_parent`
--
ALTER TABLE `student_parent`
  ADD PRIMARY KEY (`student_id`,`parent_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `teacher_subject_class`
--
ALTER TABLE `teacher_subject_class`
  ADD PRIMARY KEY (`teacher_id`,`subject_id`,`class_id`),
  ADD KEY `idx_teacher_subject_class_teacher` (`teacher_id`),
  ADD KEY `idx_teacher_subject_class_class` (`class_id`),
  ADD KEY `idx_teacher_subject_class_subject` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_record`
--
ALTER TABLE `academic_record`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_components`
--
ALTER TABLE `grade_components`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_summary`
--
ALTER TABLE `grade_summary`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_record`
--
ALTER TABLE `academic_record`
  ADD CONSTRAINT `academic_record_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `academic_record_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academic_record_ibfk_3` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`);

--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement`
  ADD CONSTRAINT `agreement_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`),
  ADD CONSTRAINT `agreement_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`academic_record_id`) REFERENCES `academic_record` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`),
  ADD CONSTRAINT `class_ibfk_2` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`),
  ADD CONSTRAINT `class_ibfk_3` FOREIGN KEY (`mentor_teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD CONSTRAINT `grade_components_ibfk_1` FOREIGN KEY (`academic_record_id`) REFERENCES `academic_record` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_components_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grade_summary`
--
ALTER TABLE `grade_summary`
  ADD CONSTRAINT `grade_summary_ibfk_1` FOREIGN KEY (`academic_record_id`) REFERENCES `academic_record` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_summary_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `school`
--
ALTER TABLE `school`
  ADD CONSTRAINT `school_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`);

--
-- Constraints for table `student_parent`
--
ALTER TABLE `student_parent`
  ADD CONSTRAINT `student_parent_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_parent_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `teacher_subject_class`
--
ALTER TABLE `teacher_subject_class`
  ADD CONSTRAINT `teacher_subject_class_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `teacher_subject_class_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `teacher_subject_class_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
