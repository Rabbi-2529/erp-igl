-- LC Management (import Letter of Credit) tables.
-- Run once on each database (this app does not use the Laravel migrations table).
-- Same schema as database/migrations/2026_11_05_000000_create_lc_management_tables.php

-- Currencies the LC files can use (BDT, USD, ... with a default rate to BDT)
CREATE TABLE IF NOT EXISTS `lc_currencies` (
  `lcc_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcc_company_id` int unsigned NOT NULL,
  `lcc_code` varchar(10) NOT NULL COMMENT 'BDT, USD, ...',
  `lcc_name` varchar(50) DEFAULT NULL,
  `lcc_rate` decimal(12,4) NOT NULL DEFAULT 1.0000 COMMENT 'default rate to BDT',
  `lcc_status` tinyint NOT NULL DEFAULT 1,
  `lcc_created_by` int unsigned DEFAULT NULL,
  `lcc_created_at` datetime DEFAULT NULL,
  `lcc_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lcc_id`),
  UNIQUE KEY `lc_currencies_company_code` (`lcc_company_id`, `lcc_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Import file opened against a supplier's proforma invoice (PI)
CREATE TABLE IF NOT EXISTS `lc_files` (
  `lcf_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcf_company_id` int unsigned NOT NULL,
  `lcf_branch_id` int unsigned NOT NULL,
  `lcf_file_no` varchar(50) NOT NULL,
  `lcf_file_date` date NOT NULL,
  `lcf_supplier_id` int unsigned NOT NULL COMMENT 'inv_suppliers.inv_sup_id',
  `lcf_pi_no` varchar(100) DEFAULT NULL,
  `lcf_pi_date` date DEFAULT NULL,
  `lcf_currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `lcf_rate` decimal(12,4) NOT NULL DEFAULT 1.0000 COMMENT 'rate to BDT',
  `lcf_total_fc` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'PI value in the file currency',
  `lcf_total_bdt` decimal(14,2) NOT NULL DEFAULT 0.00,
  `lcf_remarks` varchar(255) DEFAULT NULL,
  `lcf_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=open, 2=LC opened, 0=cancelled',
  `lcf_submit_by` int unsigned DEFAULT NULL,
  `lcf_submit_at` datetime DEFAULT NULL,
  `lcf_updated_by` int unsigned DEFAULT NULL,
  `lcf_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lcf_id`),
  UNIQUE KEY `lc_files_company_file_no` (`lcf_company_id`, `lcf_file_no`),
  KEY `lc_files_supplier` (`lcf_supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products on the PI
CREATE TABLE IF NOT EXISTS `lc_file_items` (
  `lcfi_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcfi_file_id` int unsigned NOT NULL,
  `lcfi_pro_det_id` int unsigned NOT NULL COMMENT 'inv_product_details.inv_pro_det_id',
  `lcfi_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `lcfi_unit_price_fc` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `lcfi_total_fc` decimal(14,2) NOT NULL DEFAULT 0.00,
  `lcfi_shipped_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `lcfi_received_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`lcfi_id`),
  KEY `lc_file_items_file` (`lcfi_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The LC opened at the bank for a file (one LC per file)
CREATE TABLE IF NOT EXISTS `lc_lcs` (
  `lc_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lc_company_id` int unsigned NOT NULL,
  `lc_branch_id` int unsigned NOT NULL,
  `lc_file_id` int unsigned NOT NULL,
  `lc_no` varchar(100) NOT NULL,
  `lc_date` date NOT NULL,
  `lc_bank_id` int unsigned NOT NULL COMMENT 'inv_acc_bank_infos.inv_abi_id (issuing bank)',
  `lc_value_fc` decimal(14,2) NOT NULL DEFAULT 0.00,
  `lc_rate` decimal(12,4) NOT NULL DEFAULT 1.0000,
  `lc_value_bdt` decimal(14,2) NOT NULL DEFAULT 0.00,
  `lc_margin_percent` decimal(6,2) NOT NULL DEFAULT 0.00,
  `lc_expiry_date` date DEFAULT NULL,
  `lc_last_ship_date` date DEFAULT NULL,
  `lc_remarks` varchar(255) DEFAULT NULL,
  `lc_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=open, 2=closed',
  `lc_submit_by` int unsigned DEFAULT NULL,
  `lc_submit_at` datetime DEFAULT NULL,
  `lc_updated_by` int unsigned DEFAULT NULL,
  `lc_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lc_id`),
  UNIQUE KEY `lc_lcs_file` (`lc_file_id`),
  UNIQUE KEY `lc_lcs_company_lc_no` (`lc_company_id`, `lc_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Every payment / cost on an LC (each one is also a bank statement row, type 13)
CREATE TABLE IF NOT EXISTS `lc_costs` (
  `lcx_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcx_company_id` int unsigned NOT NULL,
  `lcx_branch_id` int unsigned NOT NULL,
  `lcx_lc_id` int unsigned NOT NULL,
  `lcx_date` date NOT NULL,
  `lcx_head` tinyint NOT NULL COMMENT '1=margin, 2=bank charge, 3=insurance, 4=retirement, 5=duty & VAT, 6=C&F, 7=port & shipping, 8=transport, 9=other',
  `lcx_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'BDT',
  `lcx_bank_id` int unsigned NOT NULL COMMENT 'paid from (bank / cash account)',
  `lcx_bank_statement_id` bigint unsigned DEFAULT NULL,
  `lcx_supplier_inv_id` int unsigned DEFAULT NULL COMMENT 'supplier ledger row for goods payments',
  `lcx_voucher_no` varchar(50) DEFAULT NULL,
  `lcx_description` varchar(255) DEFAULT NULL,
  `lcx_status` tinyint NOT NULL DEFAULT 1,
  `lcx_submit_by` int unsigned DEFAULT NULL,
  `lcx_submit_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lcx_id`),
  KEY `lc_costs_lc` (`lcx_lc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploaded LC papers (stored under storage/app/lc_documents)
CREATE TABLE IF NOT EXISTS `lc_documents` (
  `lcd_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcd_company_id` int unsigned NOT NULL,
  `lcd_branch_id` int unsigned NOT NULL,
  `lcd_lc_id` int unsigned NOT NULL,
  `lcd_type` varchar(50) NOT NULL,
  `lcd_title` varchar(150) DEFAULT NULL,
  `lcd_file` varchar(255) NOT NULL,
  `lcd_original_name` varchar(255) DEFAULT NULL,
  `lcd_status` tinyint NOT NULL DEFAULT 1,
  `lcd_submit_by` int unsigned DEFAULT NULL,
  `lcd_submit_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lcd_id`),
  KEY `lc_documents_lc` (`lcd_lc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipments under an LC
CREATE TABLE IF NOT EXISTS `lc_shipments` (
  `lcs_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcs_company_id` int unsigned NOT NULL,
  `lcs_branch_id` int unsigned NOT NULL,
  `lcs_lc_id` int unsigned NOT NULL,
  `lcs_no` int unsigned NOT NULL DEFAULT 1 COMMENT 'shipment number within the LC',
  `lcs_bl_no` varchar(100) DEFAULT NULL,
  `lcs_vessel` varchar(100) DEFAULT NULL,
  `lcs_invoice_no` varchar(100) DEFAULT NULL COMMENT 'commercial invoice',
  `lcs_ship_date` date DEFAULT NULL,
  `lcs_eta` date DEFAULT NULL,
  `lcs_arrival_date` date DEFAULT NULL,
  `lcs_remarks` varchar(255) DEFAULT NULL,
  `lcs_status` tinyint NOT NULL DEFAULT 1 COMMENT '1=in transit, 2=received into stock',
  `lcs_purchase_invoice_no` varchar(100) DEFAULT NULL,
  `lcs_received_by` int unsigned DEFAULT NULL,
  `lcs_received_at` datetime DEFAULT NULL,
  `lcs_submit_by` int unsigned DEFAULT NULL,
  `lcs_submit_at` datetime DEFAULT NULL,
  PRIMARY KEY (`lcs_id`),
  KEY `lc_shipments_lc` (`lcs_lc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lc_shipment_items` (
  `lcsi_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lcsi_shipment_id` int unsigned NOT NULL,
  `lcsi_file_item_id` int unsigned NOT NULL,
  `lcsi_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`lcsi_id`),
  KEY `lc_shipment_items_shipment` (`lcsi_shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
