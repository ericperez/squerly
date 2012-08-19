<?php
/**
  *
  * Squerly - SQL Helper class
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
class SQL {

  /**
   *
   * Strips all the comments off of a SQL query
   * 
   * @param string $sql input SQL
   * @return string Input SQL with all comments stripped off
   *
   */
  public static function stripComments($sql) {
    return trim(preg_replace('/(--.*)|(((\/\*)+?[\w\W]+?(\*\/)+))/', '', $sql));
  }


  /**
   *
   * Sets or replaces the LIMIT clause value for a given SQL query
   * 
   * @param string $sql Input SQL
   * @param int $new_limit New LIMIT clause value
   * @return string SQL with new LIMIT clause set
   *
   */
  public static function overrideLimit($sql, $new_limit) {
    $new_limit_string = "\nLIMIT {$new_limit}";
    $limit_regex = '/LIMIT[\s]+[0-9]+/i';
    $offset_regex = 'OFFSET[\s]+[0-9]+/i';
    //Query has existing LIMIT clause; replace it
    $new_sql = preg_replace(array($limit_regex, $offset_regex), array($new_limit_string, "\n"), $sql);
    //Query did not have existing LIMIT clause; add one
    if(!preg_match($limit_regex, $sql)) {
      $new_sql .= $new_limit_string;
    }
    return $new_sql;
  }


  /**
   *
   * Generates an array suitable for building an HTML SELECT 'option list' from a database query
   * 
   * @param string $query SQL SELECT query to fetch option list values [first col becomes array key (should be unique); second col becomes array value]
   * @param string $DBC Database connection name
   * @return array
   * 
   * @todo find a more appropriate place for this
   * 
   */
  public static function DBOptionlist($query, $DBC = 'DB') { 
    DB::sql($query, NULL, 0, $DBC);
    $output = array();
    foreach(F3::get("${DBC}->result") as $row => $values) {
      $val_temp = array_values($values);
      if(!isset($val_temp[0]) || !isset($val_temp[1])) { F3::error('', 'SQL::DBOptionlist query must return two columns.'); }
      $val_temp[0] = htmlentities($val_temp[0]);
      $val_temp[1] = htmlentities($val_temp[1]);
      $output[$val_temp[0]] = $val_temp[1];
    }
    F3::clear("${DBC}->result");
    return $output;
  }


  /**
   *
   * Builds a SQL 'WHERE' clause string from an array
   * 
   * @param string $table Database table name
   * @param array $query_params Array of key/value pairs to convert to 'WHERE' conditions
   * @param string $DBC Database connection name
   * @return $array
   * 
   * @todo Find a more appropriate place for this
   * @todo Update this to allow different conditional operators for each $query_params
   * @todo Set values as bound parameters
   * 
   */
  public static function buildWhereFromArray($table, array $query_params, $DBC = 'DB') {
    if(empty($query_params)) { return ''; }
    $db_fields = Db_Meta::columns($table, $DBC);
    $where = array();
    foreach($query_params as $key => $val) {
      //Don't bother with fields that don't exist on the table or non-numeric values in numeric fields
      $is_numeric_field = isset($db_fields[$key]) ? Db_Meta::isNumericColumnType($db_fields[$key]) : false;
      $is_numeric_val = is_numeric($val);
      if($val === '' || !isset($db_fields[$key]) || ($is_numeric_field && !$is_numeric_val)) { 
        continue; 
      }
      //Numeric compare
      if($is_numeric_field && $is_numeric_val) { 
        $where[] = " {$key} = {$val} "; 
      } else { //String compare
        $val = addslashes($val); //TODO: make this more robust
        $where[] = " LOWER({$key}) LIKE LOWER('%{$val}%') ";
      }
    }
    return join($where, ' AND ');
  }


 /**
  *
  * Prevents the class from being instantiated--all of it's methods should be called statically
  *
  */
  final private function __construct() {}


}
