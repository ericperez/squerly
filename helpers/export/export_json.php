<?php
/**
  *
  * Squerly - JSON file export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Json implements Export_Interface {

/**
  *
  * Renders 2D array in JSON format
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename Name of file that will hold the JSON results
  * @param array $config Array of configuration settings (currently unused)
  * @return string JSON-encoded string representing input data
  *
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    //Make sure 'integer' and 'floating point' number string values are converted into actual integers/floats
    //The constant flag JSON_NUMERIC_CHECK for json_encode is supposed to do this but I ran into problems using it
    foreach($data as &$row) {
      foreach($row as &$v) {
        if(is_numeric($v)) {
          if((string) floatval($v) === $v) {
            $v = (float) $v;
          }
          if((string) intval($v) === $v) {
            $v = (int) $v;
          }
        }
      }
    }
    $json = json_encode($data); //, JSON_NUMERIC_CHECK);
    return $json;
  }

}
