-- Accounts Phase 3b: settings (VAT, voucher approval), fiscal year closing, audit trail, voucher approvals, budgets.
-- Five NEW tables only. Existing tables and rows are not changed (a VAT payment is a new bank / cash movement row,
-- like every other payment). Run accounts_phase3a.sql first. Safe to run more than once.

-- Accounts settings of a company (VAT and voucher approval), as JSON
CREATE TABLE IF NOT EXISTS `acc_settings` (
  `as_company_id` int unsigned NOT NULL,
  `as_settings` text NOT NULL,
  `as_updated_by` int unsigned DEFAULT NULL,
  `as_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`as_company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fiscal years that were closed (July to June). Closing moves the year's profit to retained earnings
-- and stops journal vouchers dated in it. A year can be reopened with a reason.
CREATE TABLE IF NOT EXISTS `acc_fiscal_years` (
  `afy_id` int unsigned NOT NULL AUTO_INCREMENT,
  `afy_company_id` int unsigned NOT NULL,
  `afy_branch_id` int unsigned NOT NULL,
  `afy_start` date NOT NULL,
  `afy_end` date NOT NULL,
  `afy_status` varchar(10) NOT NULL DEFAULT 'closed' COMMENT 'closed | reopened',
  `afy_profit` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'profit of the year when it was closed',
  `afy_note` varchar(255) DEFAULT NULL,
  `afy_closed_by` int unsigned DEFAULT NULL,
  `afy_closed_at` datetime DEFAULT NULL,
  `afy_reopened_by` int unsigned DEFAULT NULL,
  `afy_reopened_at` datetime DEFAULT NULL,
  `afy_reopen_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`afy_id`),
  UNIQUE KEY `acc_fiscal_years_year` (`afy_company_id`, `afy_branch_id`, `afy_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail: who did what in Accounts, and when
CREATE TABLE IF NOT EXISTS `acc_audit_log` (
  `aal_id` int unsigned NOT NULL AUTO_INCREMENT,
  `aal_company_id` int unsigned NOT NULL,
  `aal_branch_id` int unsigned DEFAULT NULL,
  `aal_user_id` int unsigned DEFAULT NULL,
  `aal_action` varchar(40) NOT NULL,
  `aal_entity` varchar(30) DEFAULT NULL,
  `aal_entity_id` varchar(40) DEFAULT NULL,
  `aal_summary` varchar(255) NOT NULL,
  `aal_amount` decimal(14,2) DEFAULT NULL,
  `aal_details` text DEFAULT NULL,
  `aal_ip` varchar(45) DEFAULT NULL,
  `aal_created_at` datetime NOT NULL,
  PRIMARY KEY (`aal_id`),
  KEY `acc_audit_log_when` (`aal_company_id`, `aal_branch_id`, `aal_created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval history of journal vouchers (submitted, approved, rejected)
CREATE TABLE IF NOT EXISTS `acc_journal_approvals` (
  `aja_id` int unsigned NOT NULL AUTO_INCREMENT,
  `aja_journal_id` int unsigned NOT NULL,
  `aja_action` varchar(10) NOT NULL COMMENT 'submitted | approved | rejected',
  `aja_user_id` int unsigned DEFAULT NULL,
  `aja_reason` varchar(255) DEFAULT NULL,
  `aja_created_at` datetime NOT NULL,
  PRIMARY KEY (`aja_id`),
  KEY `acc_journal_approvals_journal` (`aja_journal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Budgets: a yearly amount for an income or expense account (fiscal year starting 1 July of afy_year)
CREATE TABLE IF NOT EXISTS `acc_budgets` (
  `ab_id` int unsigned NOT NULL AUTO_INCREMENT,
  `ab_company_id` int unsigned NOT NULL,
  `ab_branch_id` int unsigned NOT NULL,
  `ab_fy_year` smallint unsigned NOT NULL COMMENT 'year the fiscal year starts in',
  `ab_account_key` varchar(40) NOT NULL,
  `ab_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ab_note` varchar(255) DEFAULT NULL,
  `ab_updated_by` int unsigned DEFAULT NULL,
  `ab_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ab_id`),
  UNIQUE KEY `acc_budgets_line` (`ab_company_id`, `ab_branch_id`, `ab_fy_year`, `ab_account_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
