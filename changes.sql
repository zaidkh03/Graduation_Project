ALTER TABLE `admins`
  DROP `admin_dashboard_data`;

  ALTER TABLE `teachers`
  DROP `teacher_dashboard_data`;

  ALTER TABLE `students`
  DROP `student_dashboard_data`;

  DROP TABLE `test`.`school_analytics_json`;