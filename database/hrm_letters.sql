-- HRM Letters: letter types, templates, letters, approval history, signatories, letterhead PAD and module roles.
-- Seven NEW tables only. Existing tables and rows are not changed. Safe to run more than once.

-- Letter types with their reference prefix (APP, OFF, SAL ...). The 23 standard types are added by the ERP the first time it is opened.
CREATE TABLE IF NOT EXISTS `hrm_letter_types` (
  `hlt_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hlt_company_id` int unsigned NOT NULL,
  `hlt_code` varchar(30) DEFAULT NULL COMMENT 'standard type (appointment, offer ...); NULL for types added by an admin',
  `hlt_name` varchar(100) NOT NULL,
  `hlt_prefix` varchar(6) NOT NULL COMMENT 'reference prefix: PREFIXYYYYMMDDXXX',
  `hlt_subject` varchar(200) DEFAULT NULL COMMENT 'default subject line',
  `hlt_fields` varchar(255) DEFAULT NULL COMMENT 'extra fields the letter asks for, comma separated (effective_date, new_salary ...)',
  `hlt_needs_review` tinyint NOT NULL DEFAULT 0 COMMENT '1 = the department head reviews it before approval',
  `hlt_sort` smallint unsigned NOT NULL DEFAULT 0,
  `hlt_status` tinyint NOT NULL DEFAULT 1,
  `hlt_created_by` int unsigned DEFAULT NULL,
  `hlt_created_at` datetime DEFAULT NULL,
  `hlt_updated_by` int unsigned DEFAULT NULL,
  `hlt_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hlt_id`),
  UNIQUE KEY `hrm_letter_types_prefix` (`hlt_company_id`, `hlt_prefix`),
  UNIQUE KEY `hrm_letter_types_code` (`hlt_company_id`, `hlt_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Letter templates: body text with placeholders like {{employee_name}}
CREATE TABLE IF NOT EXISTS `hrm_letter_templates` (
  `hltp_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hltp_company_id` int unsigned NOT NULL,
  `hltp_type_id` int unsigned NOT NULL,
  `hltp_name` varchar(100) NOT NULL,
  `hltp_subject` varchar(200) DEFAULT NULL,
  `hltp_body` mediumtext NOT NULL,
  `hltp_lang` varchar(5) NOT NULL DEFAULT 'en',
  `hltp_is_default` tinyint NOT NULL DEFAULT 0,
  `hltp_status` tinyint NOT NULL DEFAULT 1,
  `hltp_created_by` int unsigned DEFAULT NULL,
  `hltp_created_at` datetime DEFAULT NULL,
  `hltp_updated_by` int unsigned DEFAULT NULL,
  `hltp_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hltp_id`),
  KEY `hrm_letter_templates_type` (`hltp_company_id`, `hltp_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Letters. Content, employee details, letterhead and signatory are kept as they were when the letter was made / approved,
-- so later changes to the employee, template or letterhead never change an issued letter.
CREATE TABLE IF NOT EXISTS `hrm_letters` (
  `hl_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hl_company_id` int unsigned NOT NULL,
  `hl_branch_id` int unsigned NOT NULL,
  `hl_type_id` int unsigned NOT NULL,
  `hl_template_id` int unsigned DEFAULT NULL,
  `hl_employee_id` int unsigned NOT NULL COMMENT 'admin_user.au_id',
  `hl_reference` varchar(30) NOT NULL COMMENT 'PREFIXYYYYMMDDXXX (no dashes), never reused',
  `hl_letter_date` date NOT NULL,
  `hl_subject` varchar(255) DEFAULT NULL,
  `hl_body` mediumtext DEFAULT NULL COMMENT 'final letter text (placeholders filled)',
  `hl_employee` text DEFAULT NULL COMMENT 'employee details as JSON (name, ID, designation, department, joining date, salary ...)',
  `hl_fields` text DEFAULT NULL COMMENT 'extra fields as JSON (effective date, new salary ...)',
  `hl_letterhead` text DEFAULT NULL COMMENT 'letterhead PAD as JSON, frozen when approved',
  `hl_signatory_id` int unsigned DEFAULT NULL,
  `hl_signatory` text DEFAULT NULL COMMENT 'signatory as JSON, frozen when approved',
  `hl_status` varchar(12) NOT NULL DEFAULT 'draft' COMMENT 'draft | submitted | reviewed | approved | rejected | issued | cancelled',
  `hl_reviewer_id` int unsigned DEFAULT NULL,
  `hl_approver_id` int unsigned DEFAULT NULL,
  `hl_approved_at` datetime DEFAULT NULL,
  `hl_issued_at` datetime DEFAULT NULL,
  `hl_print_count` int unsigned NOT NULL DEFAULT 0,
  `hl_download_count` int unsigned NOT NULL DEFAULT 0,
  `hl_note` varchar(255) DEFAULT NULL COMMENT 'reason when rejected or cancelled',
  `hl_created_by` int unsigned DEFAULT NULL,
  `hl_created_at` datetime DEFAULT NULL,
  `hl_updated_by` int unsigned DEFAULT NULL,
  `hl_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hl_id`),
  UNIQUE KEY `hrm_letters_reference` (`hl_company_id`, `hl_reference`),
  KEY `hrm_letters_status` (`hl_company_id`, `hl_branch_id`, `hl_status`),
  KEY `hrm_letters_employee` (`hl_company_id`, `hl_employee_id`),
  KEY `hrm_letters_type_date` (`hl_company_id`, `hl_type_id`, `hl_letter_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- History of every letter: made, edited, submitted, reviewed, approved, rejected, issued, printed, downloaded, cancelled
CREATE TABLE IF NOT EXISTS `hrm_letter_approvals` (
  `hla_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hla_letter_id` int unsigned NOT NULL,
  `hla_action` varchar(20) NOT NULL,
  `hla_from_status` varchar(12) DEFAULT NULL,
  `hla_to_status` varchar(12) DEFAULT NULL,
  `hla_user_id` int unsigned DEFAULT NULL,
  `hla_note` varchar(255) DEFAULT NULL,
  `hla_ip` varchar(45) DEFAULT NULL,
  `hla_created_at` datetime NOT NULL,
  PRIMARY KEY (`hla_id`),
  KEY `hrm_letter_approvals_letter` (`hla_letter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- People who sign letters (name, designation, signature image); can_approve = may approve letters
CREATE TABLE IF NOT EXISTS `hrm_letter_signatories` (
  `hls_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hls_company_id` int unsigned NOT NULL,
  `hls_branch_id` int unsigned NOT NULL DEFAULT 0 COMMENT '0 = every branch',
  `hls_user_id` int unsigned DEFAULT NULL COMMENT 'admin_user.au_id, needed to approve',
  `hls_name` varchar(100) NOT NULL,
  `hls_designation` varchar(100) NOT NULL,
  `hls_signature` varchar(255) DEFAULT NULL COMMENT 'image path',
  `hls_can_approve` tinyint NOT NULL DEFAULT 0,
  `hls_is_default` tinyint NOT NULL DEFAULT 0,
  `hls_status` tinyint NOT NULL DEFAULT 1,
  `hls_created_by` int unsigned DEFAULT NULL,
  `hls_created_at` datetime DEFAULT NULL,
  `hls_updated_by` int unsigned DEFAULT NULL,
  `hls_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hls_id`),
  KEY `hrm_letter_signatories_company` (`hls_company_id`, `hls_branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Letterhead PAD of the company (branch 0) and optional branch letterheads
CREATE TABLE IF NOT EXISTS `hrm_company_letterheads` (
  `hlh_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hlh_company_id` int unsigned NOT NULL,
  `hlh_branch_id` int unsigned NOT NULL DEFAULT 0 COMMENT '0 = company letterhead, used by branches without their own',
  `hlh_company_name` varchar(150) DEFAULT NULL,
  `hlh_tagline` varchar(200) DEFAULT NULL,
  `hlh_address` varchar(255) DEFAULT NULL,
  `hlh_phone` varchar(100) DEFAULT NULL,
  `hlh_email` varchar(150) DEFAULT NULL,
  `hlh_website` varchar(150) DEFAULT NULL,
  `hlh_logo` varchar(255) DEFAULT NULL COMMENT 'image path',
  `hlh_seal` varchar(255) DEFAULT NULL COMMENT 'image path',
  `hlh_footer_text` varchar(255) DEFAULT NULL,
  `hlh_accent` varchar(7) DEFAULT NULL COMMENT 'colour of the header line, e.g. #0e8a43',
  `hlh_margin_top` tinyint unsigned NOT NULL DEFAULT 12 COMMENT 'mm',
  `hlh_margin_bottom` tinyint unsigned NOT NULL DEFAULT 12,
  `hlh_margin_side` tinyint unsigned NOT NULL DEFAULT 18,
  `hlh_page_numbers` tinyint NOT NULL DEFAULT 1,
  `hlh_updated_by` int unsigned DEFAULT NULL,
  `hlh_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hlh_id`),
  UNIQUE KEY `hrm_company_letterheads_branch` (`hlh_company_id`, `hlh_branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Who does what in the letter module (company admins are always HR Admin; department heads come from the employee record)
CREATE TABLE IF NOT EXISTS `hrm_letter_roles` (
  `hlr_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hlr_company_id` int unsigned NOT NULL,
  `hlr_user_id` int unsigned NOT NULL,
  `hlr_role` varchar(20) NOT NULL COMMENT 'hr_admin | hr_executive',
  `hlr_created_by` int unsigned DEFAULT NULL,
  `hlr_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`hlr_id`),
  UNIQUE KEY `hrm_letter_roles_user` (`hlr_company_id`, `hlr_user_id`, `hlr_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
