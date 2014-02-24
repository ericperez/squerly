-- Primary Report Table
CREATE TABLE `report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT '',
  `enabled` boolean DEFAULT 1,
  `type` VARCHAR(63) NOT NULL,
  `db_adapter` VARCHAR(255),
  `input_data_uri` VARCHAR(1023),
  `query` TEXT,
  `postprocess_code` TEXT,
  `keywords` VARCHAR(255) DEFAULT '',
  `hidden_from_ui` BOOLEAN DEFAULT TRUE,
  `form_method` VARCHAR(4) DEFAULT 'POST',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT NOW(),
  PRIMARY KEY (`id`)
) ENGINE=innoDB;


-- Saved Report Form Configurations
CREATE TABLE `saved_report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` VARCHAR(1023) DEFAULT '',
  `enabled` boolean DEFAULT 1,
  `report_id` INTEGER NOT NULL,
  `input_values` varchar(4095),
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT NOW(),
  PRIMARY KEY (`id`)
) ENGINE=innoDB;
