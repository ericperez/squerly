-- Main Report Table
CREATE TABLE 'report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,
  'name' varchar(255) NOT NULL,
  'type' varchar(63) NOT NULL,
  'notes' varchar(255) DEFAULT '',
  'keywords' varchar(255) DEFAULT '',
  'hidden_from_ui' BOOLEAN DEFAULT false,
  'enabled' BOOLEAN DEFAULT true,
  'db_adapter' VARCHAR(255),
  'input_data_uri' VARCHAR(4095),
  'query' text,
  'postprocess_code' text,
  'created_at' DATETIME DEFAULT DATETIME('now', 'localtime'),
  'updated_at' DATETIME DEFAULT NULL,
);
