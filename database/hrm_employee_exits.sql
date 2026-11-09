-- HRM: when an employee leaves (resigned, terminated ...), HR records the exit here.
-- The employee's record and all their history (attendance, salary, leave, letters ...) stay with them, and their
-- Employee ID and office mobile can then be given to a NEW employee, who gets a new record of their own.
-- One NEW table only. Existing tables and rows are not changed. Safe to run more than once.

CREATE TABLE IF NOT EXISTS `hrm_employee_exits` (
  `hee_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hee_company_id` int unsigned NOT NULL,
  `hee_branch_id` int unsigned NOT NULL DEFAULT 0,
  `hee_user_id` int unsigned NOT NULL COMMENT 'admin_user.au_id of the employee who left',
  `hee_employee_code` varchar(30) DEFAULT NULL COMMENT 'the Employee ID (au_user_id) they had when they left',
  `hee_mobile` varchar(20) DEFAULT NULL COMMENT 'the office mobile (au_mobile) they had when they left',
  `hee_name` varchar(100) DEFAULT NULL COMMENT 'their name when they left',
  `hee_last_day` date NOT NULL COMMENT 'last working day',
  `hee_type` varchar(20) NOT NULL COMMENT 'resigned | terminated | contract_end | other',
  `hee_reason` varchar(500) DEFAULT NULL,
  `hee_status_before` tinyint DEFAULT NULL COMMENT 'admin_user.au_status before the exit',
  `hee_created_by` int unsigned NOT NULL,
  `hee_created_at` datetime NOT NULL,
  `hee_cancelled_by` int unsigned DEFAULT NULL COMMENT 'set when the exit was undone (the employee was turned on again)',
  `hee_cancelled_at` datetime DEFAULT NULL,
  `hee_cancel_note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`hee_id`),
  KEY `hrm_employee_exits_user` (`hee_user_id`, `hee_cancelled_at`),
  KEY `hrm_employee_exits_code` (`hee_company_id`, `hee_employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
