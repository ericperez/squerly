<?php
/**
  *
  * Squerly - Data Source/Import Helpers
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  * @todo refactor this as a factory/plugin loader?
  * @todo Add more import methods for Google Spreadsheets, MongoDB, CouchDB, Hive, etc.
  * 
  */
class Data_Source {

 /**
  *
  * SQL SELECT import method
  * 
  * This method loads data from a MySQL, PostgreSQL, or SQLite database
  *
  * @param string $bound_sql_query SQL query with named bound-parameter placeholders
  * @param array $bound_params Associate array of parameters to be bound to SQL query in $bound_sql_query
  * @param string $DBC Name of the variable that holds a reference to the database handle
  * @param int $cache_expiry Number of seconds to cache the query
  * @return array 2D associative array holding the results of the query
  *
  */
  public static function loadSQL($bound_sql_query, $bound_params, $DBC = 'DB', $cache_expiry = null) {
    //If cache not set, use default; if default not set, expire immediately
    $cache_expiry = $cache_expiry ?: F3::get('REPORT_CACHE_EXPIRE') ?: 0;
    DB::sql($bound_sql_query, $bound_params, $cache_expiry, $DBC);
    $output = F3::get("${DBC}->result");
    F3::clear("${DBC}->result");
    return $output;
  }


/**
  *
  * CSV File/URI import method
  * 
  * Loads data from a CSV file/URI and converts it into an associative array
  * 
  * @param string $file_path Local file path or URI that points to CSV data
  * @param int $max_rows Maximum number of rows of CSV data to load
  * @return array 2D associative array holding a representation of the CSV data
  *
  */
  public static function loadCSVFile($file_path, $max_rows = 10) {
    if(($handle = fopen($file_path, "r")) !== FALSE) {
      $row = 1;
      $output = array();
      while(($data = fgetcsv($handle)) !== FALSE) {
        //Build the header/column names
        if($row === 1) {
          $header = $data; 
          $row++;
          continue;
        }
        $output[] = array_combine($header, array_map('trim', $data));
        if($max_rows > 0 && $row++ > $max_rows) { break; }
      }
      fclose($handle);
    }
    return $output;
  }


/**
  *
  * JSON File/URI import method
  * 
  * Loads data from a JSON file/URI and converts it into an associative array
  * 
  * @param string $file_path Local file path or URI that points to JSON data
  * @return array 2D associative array holding a representation of the JSON data
  *
  */
  public static function loadJSONFile($file_path) {
    return json_decode(file_get_contents($file_path), true);
  }


/**
  *
  * XML File/URI import method
  * 
  * Loads data from an XML file/URI and converts it into an associative array
  * 
  * @param string $file_path Local file path or URI that points to XML data
  * @return array 2D associative array holding a representation of the XML data
  *
  */
  public static function loadXMLFile($file_path) {
    return XmlToArray::render(file_get_contents($file_path));
  }

}
