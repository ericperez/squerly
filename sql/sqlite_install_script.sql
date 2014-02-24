BEGIN TRANSACTION;

-- Primary Report Table
CREATE TABLE 'report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,  
  'name' varchar(255) NOT NULL,
  'description' VARCHAR(255) DEFAULT '',
  'enabled' boolean DEFAULT 1,
  'type' VARCHAR(63) NOT NULL,
  'db_adapter' VARCHAR(255),
  'input_data_uri' VARCHAR(1023),
  'query' TEXT,
  'postprocess_code' TEXT,
  'keywords' VARCHAR(255) DEFAULT '',
  'hidden_from_ui' BOOLEAN DEFAULT 0,
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
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);

COMMIT;
