-- Company theme colours (Theme Colors page). Run once on each database.
-- No row = the default FelnaTech theme.
CREATE TABLE IF NOT EXISTS `erp_theme_settings` (
  `ets_id` int unsigned NOT NULL AUTO_INCREMENT,
  `ets_company_id` int unsigned NOT NULL,
  `ets_settings` text NOT NULL COMMENT 'JSON: primary, accent, sidebar, search, page, text (#rrggbb), heading_font, style (felna|classic)',
  `ets_updated_by` int unsigned DEFAULT NULL,
  `ets_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ets_id`),
  UNIQUE KEY `erp_theme_settings_company` (`ets_company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
