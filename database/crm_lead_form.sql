-- CRM automatic lead form (public links: one per branch, one per employee). Safe to run more than once.
-- Only NEW tables: existing CRM data (sds_query_book etc.) is not changed. New leads are added to
-- sds_query_book exactly like a manually created customer.

CREATE TABLE IF NOT EXISTS `crm_lead_forms` (
  `clf_id` int unsigned NOT NULL AUTO_INCREMENT,
  `clf_company_id` int unsigned NOT NULL,
  `clf_branch_id` int unsigned NOT NULL,
  `clf_token` varchar(40) NOT NULL COMMENT 'secret part of the public link',
  `clf_title` varchar(120) NOT NULL DEFAULT 'Contact us',
  `clf_intro` varchar(255) DEFAULT NULL,
  `clf_title_en` varchar(120) DEFAULT NULL COMMENT 'English title (clf_title is Bangla)',
  `clf_intro_en` varchar(255) DEFAULT NULL,
  `clf_lang` varchar(2) NOT NULL DEFAULT 'bn' COMMENT 'language the form opens in: bn | en',
  `clf_find_us` int NOT NULL DEFAULT 1 COMMENT 'sds_findus id written to each lead',
  `clf_assignees` varchar(255) DEFAULT NULL COMMENT 'admin_user au_id list, comma separated; leads go to them in turn',
  `clf_next` int unsigned NOT NULL DEFAULT 0 COMMENT 'whose turn is next',
  `clf_active` tinyint NOT NULL DEFAULT 1,
  `clf_created_by` int unsigned DEFAULT NULL,
  `clf_created_at` datetime DEFAULT NULL,
  `clf_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`clf_id`),
  UNIQUE KEY `crm_lead_forms_token` (`clf_token`),
  UNIQUE KEY `crm_lead_forms_branch` (`clf_company_id`, `clf_branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `crm_lead_submissions` (
  `cls_id` int unsigned NOT NULL AUTO_INCREMENT,
  `cls_form_id` int unsigned NOT NULL,
  `cls_company_id` int unsigned NOT NULL,
  `cls_branch_id` int unsigned NOT NULL,
  `cls_qb_id` int DEFAULT NULL COMMENT 'sds_query_book lead (new or the one that already had this mobile)',
  `cls_status` varchar(12) NOT NULL COMMENT 'new | duplicate',
  `cls_name` varchar(60) DEFAULT NULL,
  `cls_mobile` varchar(15) NOT NULL,
  `cls_email` varchar(80) DEFAULT NULL,
  `cls_message` varchar(500) DEFAULT NULL,
  `cls_assigned_to` int unsigned DEFAULT NULL,
  `cls_link_id` int unsigned DEFAULT NULL COMMENT 'crm_lead_links id when sent through an employee link',
  `cls_district_id` int DEFAULT NULL,
  `cls_thana_id` int DEFAULT NULL,
  `cls_reason_id` int DEFAULT NULL,
  `cls_address` varchar(150) DEFAULT NULL,
  `cls_ip` varchar(45) DEFAULT NULL,
  `cls_created_at` datetime NOT NULL,
  PRIMARY KEY (`cls_id`),
  KEY `crm_lead_submissions_company` (`cls_company_id`, `cls_created_at`),
  KEY `crm_lead_submissions_form` (`cls_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One personal link per employee: leads sent through it belong to that employee
CREATE TABLE IF NOT EXISTS `crm_lead_links` (
  `cll_id` int unsigned NOT NULL AUTO_INCREMENT,
  `cll_company_id` int unsigned NOT NULL,
  `cll_branch_id` int unsigned NOT NULL,
  `cll_user_id` int unsigned NOT NULL COMMENT 'admin_user au_id of the employee',
  `cll_token` varchar(40) NOT NULL,
  `cll_active` tinyint NOT NULL DEFAULT 1,
  `cll_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`cll_id`),
  UNIQUE KEY `crm_lead_links_token` (`cll_token`),
  UNIQUE KEY `crm_lead_links_user` (`cll_company_id`, `cll_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
