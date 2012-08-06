<?php

class Db_Meta {
  //TODO: Add Docblocks

  public static $numeric_column_types = array('int', 'tinyint', 'bigint', 'mediumint', 'smallint', 'number',
      'float', 'decimal', 'numeric', 'float', 'double', 'long', 'integer', 'real');

  public static $boolean_column_types = array('tinyint', 'bool', 'boolean');


  //Determines which Zend_Db_Adapter class needs to be used based on the DB connection specificed in $DBC
  public static function getDBAdapterClass($DBC = 'DB') {
    $db_type = F3::get("{$DBC}->backend");
    $db_class_map = array(
      'mysql'  => 'Zend_Db_Adapter_Mysqli',
      'pgsql'  => 'Zend_Db_Adapter_Pdo_Pgsql',
      'sqlite' => 'Zend_Db_Adapter_Pdo_Sqlite',
    );
    return (isset($db_class_map[$db_type])) ? $db_class_map[$db_type] : false;
  }


  //Returns a standardized description of a database table for MySQL, PostgreSQL, and SQLite
  public static function describeTable($table, $DBC = 'DB') {
    $adapter_class = self::getDBAdapterClass($DBC);
    if(!$adapter_class) { F3::error('', 'Unsupported Database'); }
    return $adapter_class::describeTable($table);
  }


  //Returns a standardized description of a database table for MySQL, PostgreSQL, and SQLite
  public static function getTables($DBC = 'DB') {
    $adapter_class = self::getDBAdapterClass($DBC);
    if(!$adapter_class) { F3::error('', 'Unsupported Database'); }
    $tables = $adapter_class::listTables();
    $db_name = F3::get("{$DBC}->dbname");
    return Matrix::pick($tables, 'Tables_in_' . $db_name);
  }


  //Returns an array of (column name => type) or (column name) for a given database table
  public static function getColumnsOfType($table, $types, $DBC = 'DB') {
    $table_desc = self::describeTable($table, $DBC);
    $names = Matrix::pick($table_desc, 'COLUMN_NAME');
    $types = Matrix::pick($table_desc, 'DATA_TYPE');
    return ($return_type) ? array_combine($names, $types) : $names;
  }


  //Returns an array of (column name => type) or (column name) for a given database table
  public static function columns($table, $return_type = true) {
    $table_desc = self::describeTable($table);
    $names = Matrix::pick($table_desc, 'COLUMN_NAME');
    $types = Matrix::pick($table_desc, 'DATA_TYPE');
    return ($return_type) ? array_combine($names, $types) : $names;
  }


  //Determines if a given field is a boolean
  public static function isBooleanColumn($type) {
    return in_array($type, self::$boolean_column_types);
  }


  //Determines if a given column type is numeric
  //TODO: find a better way of determining this
  public static function isNumericColumn($type) {
    return in_array($type, self::$numeric_column_types);
  }


  //Returns a 'best-guess' at the 'name' column of a DB table
  public static function getNameColumn($table) {
    $table_cols = self::columns($table, false);
    $possible_name_fields = array('name', 'title', 'record_name'); //TODO: expand this
    $possible_name_fields[] = $table . '_name';
    $possible_name_fields[] = $table . '_title';
    $intersection = array_values(array_intersect($table_cols, $possible_name_fields));
    $name_fields = !empty($intersection) ? $intersection : array('');
    return $name_fields[0];
  }


  //Tries to determine the primary keys for a given database table
  public static function getPrimaryKeys($table) {
    $table_desc = self::describeTable($table);
    $primary_keys = array();
    foreach($table_desc as $field_desc) {
      if($field_desc['PRIMARY'] == true) { $primary_keys[] = $field_desc['COLUMN_NAME']; }
    }
    if(sizeof($primary_keys) === 0) { return null; }
    //return (sizeof($primary_keys) === 1) ? $primary_keys[0] : $primary_keys;
    return isset($primary_keys[0]) ? $primary_keys[0] : null; //Currently only returns first primary key field; composite keys not supported in code that calls this
  }


  //Tries to determine which table columns are foreign keys to another table by simple naming convention
  //TODO: Make this more robust; better identification of FKs, etc.
  public static function getForeignKeys($table) {
    $tables = self::getTables();
    $columns = self::columns($table, false);
    $foreign_keys = array();
    foreach($columns as $col) {
      $table_col = self::colToTable($col);
      if(in_array($table_col, $tables)) {
        $foreign_keys[] = $col;
      }
    }
    return $foreign_keys;
  }


  //Attempts to convert a column name into a Table name (for foreign keys)
  public static function colToTable($col) {
    $tables = self::getTables();
    $fk_suffixes = array('/_id$/', '/_fk$/'); //TODO: expand this
    $col_no_suffix = preg_replace($fk_suffixes, '', $col); //Drop suffixes
    //Deal with any prefixes on table names (such as 'primary_', etc.)
    foreach($tables as $table_name) {
      $regex = "/{$table_name}$/";
      if(preg_match($regex, $col_no_suffix, $col_table_match)) { return $col_table_match[0]; }
    }
    return false;
  }


  //Resolves foreign keys on record result set
  //TODO: clean this up
  public static function resolveForeignKeys(array $record_data, $DBC = 'DB') {
    $tables = Db_Meta::getTables();
    $skip_cols = array();
    $fks = array();
    $name_cols = array();
    $primary_cols = array();

    //This nested loop is optimized by using variable referencing, memoization, and DB caching
    foreach($record_data as $row => &$data) {
      foreach($data as $col => &$v) {
        //Don't bother iterating over all rows for a non-fk column
        if(isset($skip_cols[$col])) { continue; } //This is faster than using in_array
        $foreign_table = self::colToTable($col);
        if(!$foreign_table) { //Not a foreign key
          $skip_cols[$col] = '';
          continue;
        }
        else {
          $fks[$col] = $foreign_table;
        }

        //Column is a foreign key
        $table = isset($fks[$col]) ? $fks[$col] : self::colToTable();
        if(!$table || !in_array($table, $tables)) {
          continue;
        } else {
          $fks[$table] = $col;
        }
        //Find the 'name' field for a given table
        $name_col = isset($name_cols[$table]) ? $name_cols[$table] : self::getNameColumn($table);
        //Find the name of the primary key column
        if(isset($primary_cols[$table])) {
          $primary_key = $primary_cols[$table];
        } else {
          $primary_key = self::getPrimaryKeys($table);
          $primary_cols[$table] = $primary_key;
        }
        if(!$name_col || !$primary_key) { continue; } //TODO: set a warning if this happens
        $sql = "SELECT {$name_col} FROM {$table} WHERE {$primary_key} = :pk_val";
        $DBC::sql($sql, array(':pk_val' => $v), 120); //Cache result for two minutes
        $result = F3::get("{$DBC}->result");
        if(!$result) { continue; } //No match found
        $v = $result[0][$name_col];
      }
    }
    return $record_data;
  }

}