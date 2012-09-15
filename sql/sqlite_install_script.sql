-- Main Report Table

BEGIN TRANSACTION;

CREATE TABLE 'report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL,
  'name' VARCHAR(255) NOT NULL,
  'type' VARCHAR(63) NOT NULL,
  'notes' VARCHAR(255) DEFAULT '',
  'keywords' VARCHAR(255) DEFAULT '',
  'hidden_from_ui' BOOLEAN DEFAULT 0,
  'enabled' BOOLEAN DEFAULT 1,
  'db_adapter' VARCHAR(255),
  'input_data_uri' VARCHAR(4095),
  'query' TEXT,
  'postprocess_code' TEXT
  'created_at' DATETIME DEFAULT NULL,
  'updated_at' DATETIME DEFAULT (DATETIME('now','localtime'))
);

COMMIT;


CREATE TABLE 'report' (
  'id' INTEGER PRIMARY KEY ASC NOT NULL ,
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
  'created_at' DATETIME NOT NULL,
  'updated_at' DATETIME NOT NULL
);
