-- HRM Letters: employees ask HR for a letter (salary certificate, experience certificate, NOC ...).
-- Two NEW tables only. Existing tables and rows are not changed. Safe to run more than once.
-- Run after hrm_letters.sql.

-- A letter an employee asked for. HR makes the letter from it (it then follows the normal draft and approval steps) or declines it.
CREATE TABLE IF NOT EXISTS `hrm_letter_requests` (
  `hlq_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hlq_company_id` int unsigned NOT NULL,
  `hlq_branch_id` int unsigned NOT NULL DEFAULT 0,
  `hlq_employee_id` int unsigned NOT NULL COMMENT 'admin_user.au_id of the employee who asked',
  `hlq_type_id` int unsigned NOT NULL COMMENT 'hrm_letter_types.hlt_id',
  `hlq_purpose` varchar(255) NOT NULL COMMENT 'what the letter is for, e.g. bank loan, visa',
  `hlq_addressed_to` varchar(255) DEFAULT NULL COMMENT 'who the letter is for, e.g. The Manager, ABC Bank',
  `hlq_needed_by` date DEFAULT NULL,
  `hlq_note` varchar(1000) DEFAULT NULL,
  `hlq_status` varchar(12) NOT NULL DEFAULT 'pending' COMMENT 'pending | made | declined | withdrawn',
  `hlq_letter_id` int unsigned DEFAULT NULL COMMENT 'hrm_letters.hl_id once HR made the letter',
  `hlq_answer` varchar(255) DEFAULT NULL COMMENT 'reason when declined',
  `hlq_handled_by` int unsigned DEFAULT NULL,
  `hlq_handled_at` datetime DEFAULT NULL,
  `hlq_created_at` datetime DEFAULT NULL,
  `hlq_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hlq_id`),
  KEY `hrm_letter_requests_status` (`hlq_company_id`, `hlq_status`),
  KEY `hrm_letter_requests_employee` (`hlq_employee_id`),
  KEY `hrm_letter_requests_letter` (`hlq_letter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Which letter types employees may ask for, per company. A type with no row here uses the standard choice
-- (employment, experience and salary certificates, NOC, relieving, clearance and custom letters).
CREATE TABLE IF NOT EXISTS `hrm_letter_request_types` (
  `hlrt_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hlrt_company_id` int unsigned NOT NULL,
  `hlrt_type_id` int unsigned NOT NULL,
  `hlrt_allowed` tinyint NOT NULL DEFAULT 1,
  `hlrt_updated_by` int unsigned DEFAULT NULL,
  `hlrt_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hlrt_id`),
  UNIQUE KEY `hrm_letter_request_types_type` (`hlrt_company_id`, `hlrt_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
