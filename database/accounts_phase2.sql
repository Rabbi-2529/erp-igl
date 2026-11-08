-- Accounts Phase 2: two NEW tables only. Existing tables and rows are not changed.
-- (Salary payments and petty cash need no new table: they only add rows, like every other payment.)
-- Safe to run more than once.

-- Cheque register: cheques received from customers / issued to suppliers, until they clear or bounce
CREATE TABLE IF NOT EXISTS `acc_cheques` (
  `chq_id` int unsigned NOT NULL AUTO_INCREMENT,
  `chq_company_id` int unsigned NOT NULL,
  `chq_branch_id` int unsigned NOT NULL,
  `chq_direction` varchar(10) NOT NULL COMMENT 'received | issued',
  `chq_party_type` varchar(10) NOT NULL COMMENT 'customer | supplier | other',
  `chq_party_id` int unsigned DEFAULT NULL,
  `chq_party_name` varchar(120) DEFAULT NULL,
  `chq_number` varchar(40) NOT NULL,
  `chq_bank_name` varchar(120) DEFAULT NULL COMMENT 'bank written on the cheque',
  `chq_date` date NOT NULL COMMENT 'date on the cheque (a later date = post-dated)',
  `chq_amount` decimal(14,2) NOT NULL,
  `chq_account_id` int unsigned DEFAULT NULL COMMENT 'our account (inv_acc_bank_infos) it is deposited to / paid from',
  `chq_status` varchar(10) NOT NULL DEFAULT 'pending' COMMENT 'pending | cleared | bounced | cancelled',
  `chq_status_date` date DEFAULT NULL,
  `chq_statement_id` int unsigned DEFAULT NULL COMMENT 'inv_acc_bank_statements row made when it cleared',
  `chq_inventory_id` int unsigned DEFAULT NULL COMMENT 'inv_product_inventories row made when it cleared (customer / supplier ledger)',
  `chq_note` varchar(255) DEFAULT NULL,
  `chq_created_by` int unsigned DEFAULT NULL,
  `chq_created_at` datetime DEFAULT NULL,
  `chq_updated_by` int unsigned DEFAULT NULL,
  `chq_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`chq_id`),
  KEY `acc_cheques_branch` (`chq_company_id`, `chq_branch_id`, `chq_status`, `chq_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank reconciliation: one tick per bank / cash movement that also appears on the real bank statement
CREATE TABLE IF NOT EXISTS `acc_bank_recon_marks` (
  `abr_id` int unsigned NOT NULL AUTO_INCREMENT,
  `abr_company_id` int unsigned NOT NULL,
  `abr_branch_id` int unsigned NOT NULL,
  `abr_account_id` int unsigned NOT NULL,
  `abr_statement_id` int unsigned NOT NULL COMMENT 'inv_acc_bank_statements row that is matched',
  `abr_cleared_date` date DEFAULT NULL COMMENT 'date it shows on the bank statement',
  `abr_marked_by` int unsigned DEFAULT NULL,
  `abr_marked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`abr_id`),
  UNIQUE KEY `acc_bank_recon_statement` (`abr_statement_id`),
  KEY `acc_bank_recon_account` (`abr_company_id`, `abr_branch_id`, `abr_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
