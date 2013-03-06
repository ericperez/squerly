BEGIN TRANSACTION;

-- Primary Report Table
CREATE TABLE 'report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,  
  'name' varchar(255) NOT NULL,
  'description' VARCHAR(255) DEFAULT '',
  'enabled' boolean DEFAULT 1,
  'type' VARCHAR(63) NOT NULL,
  'data_source' VARCHAR(255),
  'input_data_uri' VARCHAR(4095),
  'query' TEXT,
  'postprocess_code' TEXT,
  'keywords' VARCHAR(255) DEFAULT '',
  'hidden_from_ui' BOOLEAN DEFAULT 0,
  'default_refresh_time' INT DEFAULT 0,
  'default_cache_time' INT DEFAULT 0,
  'default_export_context' VARCHAR(255) DEFAULT '',
  'form_method' VARCHAR(4) DEFAULT 'POST',
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);

-- Saved Report Form Configurations
CREATE TABLE 'saved_report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,  
  'name' varchar(255) NOT NULL,
  'description' VARCHAR(1023) DEFAULT '',
  'enabled' boolean DEFAULT 1,
  'report_id' INTEGER NOT NULL,
  'input_values' varchar(4095),
  'created_by' VARCHAR(255), 
  'created_at' DATETIME DEFAULT NULL,
  'updated_by' VARCHAR(255),
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);


-- Email distribution lists for Reports
CREATE TABLE 'email_distribution_list' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,  
  'name' varchar(255) NOT NULL,
  'description' VARCHAR(255) DEFAULT '',
  'enabled' boolean DEFAULT true,
  'email_recipients' varchar(4095) NOT NULL,
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);

-- Email Schedules for Reports
CREATE TABLE 'email_schedule' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,  
  'name' varchar(255) NOT NULL,
  'description' VARCHAR(255) DEFAULT '',
  'enabled' boolean DEFAULT true,
  'email_schedule' varchar(1023) NOT NULL,[]
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);

COMMIT;
