<?php
class Data_Source {
  //TODO: refactor this as a factory/plugin loader?

  //SQL SELECT Import
  public static function loadSQL($bound_sql_query, $bound_params, $DBC = 'DB', $cache_expiry = null) {
    //If cache not set, use default; if default not set, expire immediately
    $cache_expiry = $cache_expiry ?: F3::get('REPORT_CACHE_EXPIRE') ?: 0;
    $DBC::sql($bound_sql_query, $bound_params);
    $output = F3::get("${DBC}->result");
    F3::clear("${DBC}->result");
    return $output;
  }


  //CSV Import
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


  //Loads a JSON file and converts it into an associative array
  public static function loadJSONFile($file_path) {
    $json_array = json_decode(file_get_contents($file_path), true);
    return $json_array;
  }


  //Loads an XML file and converts it into an associative array
  public static function loadXMLFile($file_path) {
    return XmlToArray::render(file_get_contents($file_path));
  }


  /* TODO: Add Methods:
      -loadGoogleSpreadsheet
      -loadMongo
      -loadHive
      -etc.
  */
}