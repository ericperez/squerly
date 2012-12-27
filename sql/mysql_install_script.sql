

-- Primary Report Table
CREATE TABLE `report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(63) NOT NULL,
  `data_source` VARCHAR(255),
  `input_data_uri` VARCHAR(4095),
  `query` TEXT,
  `postprocess_code` TEXT,
  `description` VARCHAR(255) DEFAULT '',
  `keywords` VARCHAR(255) DEFAULT '',
  `hidden_from_ui` BOOLEAN DEFAULT 0,
  `enabled` BOOLEAN DEFAULT 1,
  `default_refresh_time` INT DEFAULT 0,
  `default_cache_time` INT DEFAULT 0,
  'default_export_context' VARCHAR(255) DEFAULT '',
  `form_action` VARCHAR(4) DEFAULT 'get',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT NOW(),
  PRIMARY KEY (`id`)
) ENGINE=innoDB;
