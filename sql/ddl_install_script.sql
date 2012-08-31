-- TODO: Make this database agnostic; currently only works for MySQL

-- Report Table
DROP DATABASE IF EXISTS squerly;
CREATE DATABASE squerly;

USE squerly;

-- This table is simply used to test the CRUD routing interface for building tables
-- DROP TABLE IF EXISTS test_table;
CREATE TABLE `test_table` (
  `test_varchar_required` varchar(255) NOT NULL,
  `test_date_required` date NOT NULL,
  `test_int` int(11) DEFAULT NULL,
  `test_tinyint` tinyint(4) DEFAULT NULL,
  `test_bool` tinyint(1) DEFAULT NULL,
  `test_smallint` smallint(6) DEFAULT NULL,
  `test_mediumint` mediumint(9) DEFAULT NULL,
  `test_bigint` bigint(20) DEFAULT NULL,
  `test_decimal` decimal(4,4) DEFAULT NULL,
  `test_float` float(4,4) DEFAULT NULL,
  `test_double` double(4,4) DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `test_datetime` datetime DEFAULT NULL,
  `test_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `test_time` time DEFAULT NULL,
  `test_year` year(4) DEFAULT NULL,
  `test_char2` char(2) DEFAULT NULL,
  `test_char100` char(100) DEFAULT NULL,
  `test_varchar255` varchar(255) DEFAULT NULL,
  `test_varchar31` varchar(31) DEFAULT NULL,
  `test_text` text
) ENGINE=innoDB;


-- DROP TABLE IF EXISTS report;
CREATE TABLE `report` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(63) NOT NULL,
  `notes` varchar(255) DEFAULT '',
  `keywords` varchar(255) DEFAULT '',
  `description` TEXT,
  `hidden_from_ui` BOOLEAN DEFAULT false,
  `enabled` BOOLEAN DEFAULT true,
  `db_adapter` VARCHAR(255),
  `input_data_uri` VARCHAR(4095),
  `query` text,
  `postprocess_code` text,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`) -- UNIQUE name??
) ENGINE=innoDB;


-- Report Config Table
-- DROP TABLE IF EXISTS report_config;
CREATE TABLE `report_config` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `report_id` int(6) NOT NULL,
  `name` varchar(255) NOT NULL,
  `notes` text,
  `form_values` varchar(4095),
  `enabled` boolean DEFAULT true,
  `send_empty_results` boolean DEFAULT false,
  `cron_schedule` varchar(20),
  `email_recipients` varchar(1023),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB;
-- TODO: Alter table add foreign key to report


-- Report Drilldown Table
-- DROP TABLE IF EXISTS report_drilldown;
CREATE TABLE `report_drilldown` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `primary_report_id` int(6) NOT NULL,
  `primary_drilldown_column` int(3) NOT NULL,
  `drilldown_report_id` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_report_drilldown` (`primary_report_id`, `primary_drilldown_column`, `drilldown_report_id`)
) ENGINE=innoDB;
-- TODO: Alter table add foreign key to report X 2


-- TODO: Report Group table


-- Input Widget Table
-- DROP TABLE IF EXISTS input_widget;
CREATE TABLE `input_widget` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `html_attributes` varchar(1023) NOT NULL, -- JSON STRING
  `widget_type` varchar(63) NOT NULL,
  `select_sql` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=innoDB;
-- UNIQUE KEY `uk_input_widget_htmlid` (`html_id`)

