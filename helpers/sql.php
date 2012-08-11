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
   * stripComments - Strips all the comments off of a SQL query
   * @param $sql string - input SQL
   * @return string - input SQL with all comments stripped off
   *
   */
  public static function stripComments($sql) {
    return trim(preg_replace('/(--.*)|(((\/\*)+?[\w\W]+?(\*\/)+))/', '', $sql));
  }


  /**
   *
   * overrideLimit - Sets or replaces the LIMIT clause value for a given SQL query
   * @param $sql string - Input SQL
   * @param $new_limit integer - New LIMIT clause value
   * @return $string - SQL with new LIMIT clause set
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
   * DBOptionlist - generates an array suitable for building an HTML SELECT 'option list' from a database query
   * @todo find a more appropriate place for this
   * @param $query string - SQL SELECT query to fetch option list values [first col becomes array key (should be unique); second col becomes array value]
   * @param $DBC string - Database connection name
   * @return $array  
   * 
   */
  public static function DBOptionlist($query, $DBC = 'DB') { 
    $DBC::sql($query);
    $output = array();
    foreach(F3::get("${DBC}->result") as $row => $values) {
      $val_temp = array_values($values);
      if(!isset($val_temp[0]) || !isset($val_temp[1])) { throw new exception('SQL::DBOptionlist query must return two columns.'); }
      $val_temp[0] = htmlentities($val_temp[0]);
      $val_temp[1] = htmlentities($val_temp[1]);
      $output[$val_temp[0]] = $val_temp[1];
    }
    F3::clear("${DBC}->result");
    return $output;
  }


  //Builds a 'WHERE' clause string from an array
  //TODO: update this to allow different conditional operators for each $query_params
  //TODO: set values as bound parameters
  public static function buildWhereFromArray($model, array $query_params) {
    if(empty($query_params)) { return ''; }
    $db_fields = Db_Meta::columns($model);
    $where = array();
    foreach($query_params as $key => $val) {
      //Don't bother with fields that don't exist on the table or non-numeric values in numeric fields
      $is_numeric_field = Db_Meta::isNumericColumn($db_fields[$key]);
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
