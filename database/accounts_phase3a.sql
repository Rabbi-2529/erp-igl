-- Accounts Phase 3a: chart of accounts and journal vouchers. Three NEW tables only.
-- Existing tables and rows are not changed: sales, purchases, payments, expenses, salary, loans and cheques
-- are read as journal entries from where they already are. Safe to run more than once.

-- Chart of accounts (one per company). System accounts are added by the ERP the first time it is opened.
CREATE TABLE IF NOT EXISTS `acc_coa` (
  `coa_id` int unsigned NOT NULL AUTO_INCREMENT,
  `coa_company_id` int unsigned NOT NULL,
  `coa_code` varchar(20) NOT NULL,
  `coa_name` varchar(150) NOT NULL,
  `coa_type` varchar(10) NOT NULL COMMENT 'asset | liability | equity | income | expense',
  `coa_parent_id` int unsigned DEFAULT NULL COMMENT 'group it is shown under',
  `coa_system_key` varchar(30) DEFAULT NULL COMMENT 'set on accounts the ERP fills itself (cash and bank, customers, sales ...)',
  `coa_manual` tinyint NOT NULL DEFAULT 1 COMMENT '1 = can be used in a journal voucher',
  `coa_status` tinyint NOT NULL DEFAULT 1,
  `coa_note` varchar(255) DEFAULT NULL,
  `coa_created_by` int unsigned DEFAULT NULL,
  `coa_created_at` datetime DEFAULT NULL,
  `coa_updated_by` int unsigned DEFAULT NULL,
  `coa_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`coa_id`),
  UNIQUE KEY `acc_coa_code` (`coa_company_id`, `coa_code`),
  UNIQUE KEY `acc_coa_system` (`coa_company_id`, `coa_system_key`),
  KEY `acc_coa_type` (`coa_company_id`, `coa_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal vouchers (manual entries: adjustments, accruals, opening balances, owner's money ...)
CREATE TABLE IF NOT EXISTS `acc_journals` (
  `aj_id` int unsigned NOT NULL AUTO_INCREMENT,
  `aj_company_id` int unsigned NOT NULL,
  `aj_branch_id` int unsigned NOT NULL,
  `aj_voucher_no` varchar(30) NOT NULL,
  `aj_date` date NOT NULL,
  `aj_narration` varchar(255) NOT NULL,
  `aj_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `aj_status` varchar(10) NOT NULL DEFAULT 'posted' COMMENT 'posted | cancelled',
  `aj_cancel_reason` varchar(255) DEFAULT NULL,
  `aj_created_by` int unsigned DEFAULT NULL,
  `aj_created_at` datetime DEFAULT NULL,
  `aj_cancelled_by` int unsigned DEFAULT NULL,
  `aj_cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`aj_id`),
  UNIQUE KEY `acc_journals_voucher` (`aj_company_id`, `aj_branch_id`, `aj_voucher_no`),
  KEY `acc_journals_date` (`aj_company_id`, `aj_branch_id`, `aj_status`, `aj_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Debit / credit lines of a journal voucher (debits always equal credits)
CREATE TABLE IF NOT EXISTS `acc_journal_lines` (
  `ajl_id` int unsigned NOT NULL AUTO_INCREMENT,
  `ajl_journal_id` int unsigned NOT NULL,
  `ajl_account_key` varchar(40) NOT NULL COMMENT 'coa:<id>, a system key, or an expense / ledger head (exp:cat:<id>, exp:ledg:<id>, inc:ledg:<id>)',
  `ajl_account_name` varchar(150) NOT NULL COMMENT 'account name when it was posted',
  `ajl_debit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ajl_credit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ajl_note` varchar(255) DEFAULT NULL,
  `ajl_sort` smallint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ajl_id`),
  KEY `acc_journal_lines_journal` (`ajl_journal_id`),
  KEY `acc_journal_lines_account` (`ajl_account_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
