<?php
/**
  *
  * Squerly - Database/Table Metadata Helper class
  * 
  * Contains methods to help with the manipulation of SQL statements/strings 
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Db_Meta {

  public static $numeric_column_types = array('int', 'tinyint', 'bigint', 'mediumint', 'smallint', 'number',
      'float', 'decimal', 'numeric', 'float', 'double', 'long', 'integer', 'real');

  public static $boolean_column_types = array('tinyint', 'bool', 'boolean');


 /**
  *
  * Determines which Zend_Db_Adapter class needs to be used based on the DB connection specificed in $DBC
  * 
  * @param string $DBC Database connection variable name
  * @return class Instance of appropriate DB Adapter
  *
  */
  public static function getDBAdapterClass($DBC = 'DB') {
    $db_type = F3::get("{$DBC}->backend");
    $db_class_map = array(
      'mysql'  => 'Zend_Db_Adapter_Mysqli',
      'pgsql'  => 'Zend_Db_Adapter_Pdo_Pgsql',
      'sqlite' => 'Zend_Db_Adapter_Pdo_Sqlite',
    );
    return (isset($db_class_map[$db_type])) ? $db_class_map[$db_type] : false;
  }


 /**
  *
  * Returns a standardized description of a database table for MySQL, PostgreSQL, and SQLite
  * 
  * @param string $table Database table name
  * @param string $DBC Database connection variable name
  * @return array Description of table properties/metadata
  *
  */
  public static function describeTable($table, $DBC = 'DB') {
    $adapter_class = self::getDBAdapterClass($DBC);
    if(!$adapter_class) { F3::error('', 'Unsupported Database'); }
    return $adapter_class::describeTable($table);
  }


 /**
  *
  * Returns an array of all database tables on a given database referenced by $DBC
  * 
  * @param string $DBC Database connection variable name
  * @return array Array of tables that exist in the database
  *
  */
  public static function getTables($DBC = 'DB') {
    $adapter_class = self::getDBAdapterClass($DBC);
    if(!$adapter_class) { F3::error('', 'Unsupported Database'); }
    $tables = $adapter_class::listTables();
    $db_name = F3::get("{$DBC}->dbname");
    $db_type = F3::get("{$DBC}->backend");
    switch($db_type) {
      case 'mysql':
        return Matrix::pick($tables, 'Tables_in_' . $db_name);
      break;

      default:
        return Matrix::pick($tables, 'name');
      break;
    }
    
  }


 /**
  *
  * Returns an array of DB columns of type $types
  * 
  * @param string $table Database table name
  * @param mixed $types String with one or array of multiple columns types
  * @return array Array of DB columns that match a given type
  * 
  * @todo Finish this
  *
  */
  public static function getColumnsOfType($table, $types, $DBC = 'DB') {
    if(!is_array($types)) { $types = array($types); }
    $table_desc = self::describeTable($table, $DBC);
    $names = Matrix::pick($table_desc, 'COLUMN_NAME');
    $types = Matrix::pick($table_desc, 'DATA_TYPE');
    return ($return_type) ? array_combine($names, $types) : $names;
  }


 /**
  *
  * Returns an array of (column name => type) or (column name) for a given database table
  * 
  * @param string $table Database table name
  * @param boolean $return_type If true, sets the values of the return array to the column type
  * @param string $DBC Database connection variable name
  * @return array Array of DB columns that match a given type
  *
  */
  public static function columns($table, $return_type = true, $DBC = 'DB') {
    $table_desc = self::describeTable($table, $DBC);
    $names = Matrix::pick($table_desc, 'COLUMN_NAME');
    $types = Matrix::pick($table_desc, 'DATA_TYPE');
    return ($return_type) ? array_combine($names, $types) : $names;
  }


 /**
  *
  * Determines if a given column type is a boolean
  * 
  * @param string $type Type of DB field to test if boolean
  * @return boolean True if type is boolean; false if not
  *
  */
  public static function isBooleanColumnType($type) {
    return in_array($type, self::$boolean_column_types);
  }


 /**
  *
  * Determines if a given column type is numeric
  * 
  * @param string $type Type of DB field to test if boolean
  * @return boolean True if type is boolean; false if not
  * 
  * @todo Find a better way of determining this
  *
  */
  public static function isNumericColumnType($type) {
    return in_array($type, self::$numeric_column_types);
  }


 /**
  *
  * Returns a 'best-guess' at the 'name' column of a DB table
  * 
  * @param string $table Database table name
  * @param string $DBC Database connection variable name
  * @return string Name Field if one is found; empty string if not
  * 
  * @todo Enhance this with more possible 'name' fields
  * @todo Add DBC var
  *
  */
  public static function getNameColumn($table, $DBC = 'DB') {
    $table_cols = self::columns($table, false, $DBC);
    $possible_name_fields = array('name', 'title', 'record_name'); //TODO: expand this
    $possible_name_fields[] = $table . '_name';
    $possible_name_fields[] = $table . '_title';
    $intersection = array_values(array_intersect($table_cols, $possible_name_fields));
    $name_fields = !empty($intersection) ? $intersection : array('');
    return $name_fields[0];
  }


 /**
  *
  * Tries to determine the primary keys for a given database table
  * 
  * @param string $table Database table name
  * @param string $DBC Database connection variable name
  * @return string Name Field if one is found; empty string if not
  * 
  */
  public static function getPrimaryKeys($table, $DBC = 'DB') {
    $table_desc = self::describeTable($table, $DBC);
    $primary_keys = array();
    foreach($table_desc as $field_desc) {
      if($field_desc['PRIMARY'] == true) { $primary_keys[] = $field_desc['COLUMN_NAME']; }
    }
    if(sizeof($primary_keys) === 0) { return null; }
    //return (sizeof($primary_keys) === 1) ? $primary_keys[0] : $primary_keys;
    return isset($primary_keys[0]) ? $primary_keys[0] : null; //Currently only returns first primary key field; composite keys not supported in code that calls this
  }


 /**
  *
  * Tries to determine which table columns are foreign keys to another table by simple naming convention
  * 
  * @param string $table Database table name
  * @param string $DBC Database connection variable name
  * @return string Name Field if one is found; empty string if not
  * 
  * @todo Make this more robust; better identification of FKs, etc.
  * 
  */
  public static function getForeignKeys($table, $DBC = 'DB') {
    $tables = self::getTables($DBC);
    $columns = self::columns($table, false, $DBC);
    $foreign_keys = array();
    //Cycle through all the columns and see if they look like table names/references
    foreach($columns as $col) {
      $table_col = self::colToTable($col);
      if($table_col !== $table && in_array($table_col, $tables)) {
        $foreign_keys[] = $col;
      }
    }
    return $foreign_keys;
  }


 /**
  *
  * Attempts to convert a column name into a Table name (for foreign keys)
  * 
  * @param string $col Database column name
  * @param string $DBC Database connection variable name
  * @return string Database table name match; false if not
  * 
  * @todo Make this more robust; better identification of FKs, etc.
  * 
  */
  public static function colToTable($col, $DBC = 'DB') {
    $tables = self::getTables($DBC);
    $fk_suffixes = array('/_id$/', '/_fk$/'); //TODO: expand this
    $col_no_suffix = preg_replace($fk_suffixes, '', $col); //Drop suffixes
    //Deal with any prefixes on table names (such as 'primary_', etc.)
    foreach($tables as $table_name) {
      $regex = "/{$table_name}$/";
      if(preg_match($regex, $col_no_suffix, $col_table_match)) { return $col_table_match[0]; }
    }
    return false;
  }



 /**
  *
  * Resolves foreign keys on record result set to convert foreign IDs to names/labels
  * 
  * @param array $record_data
  * @param boolean $id_in_name - If true, the ID of the model will be prepended on the name
  * @param string $DBC Database connection variable name
  * @return array Record Data with foreign keys IDs resolved to names/labels
  * 
  * @todo Clean this up
  * 
  */
  public static function resolveForeignKeys(array $record_data, $id_in_name = true, $DBC = 'DB') {
    $tables = Db_Meta::getTables($DBC);
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
        //No foreign table found
        if(!$foreign_table) {
          $skip_cols[$col] = null;
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
        if(!$v || !$name_col || !$primary_key) { continue; }

        //TODO: Consolidate this code using the F3 'DB' class
        $db_type = F3::get("{$DBC}->backend");
        switch($db_type) {
          case 'pgsql':
          case 'sqlite':
            $sql = ($id_in_name) ? 
              "SELECT '[{$v}] ' || {$name_col} AS {$name_col} FROM {$table} WHERE {$primary_key} = :pk_val" :
              "SELECT {$name_col} FROM {$table} WHERE {$primary_key} = :pk_val";
          break;

          case 'mysql':
          default:
            $sql = ($id_in_name) ? 
              "SELECT CONCAT('[', '{$v}', '] ', {$name_col}) AS {$name_col} FROM {$table} WHERE {$primary_key} = :pk_val" :
              "SELECT {$name_col} FROM {$table} WHERE {$primary_key} = :pk_val";
          break;   
        }

        DB::sql($sql, array(':pk_val' => $v), 60, $DBC); //Cache result for one minute
        $result = F3::get("{$DBC}->result");
        if(!$result) { continue; } //No match found
        $v = $result[0][$name_col];
      }
    }
    return $record_data;
  }

}
