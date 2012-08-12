<?php
/**
  *
  * Squerly - JSON file export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
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
  * @param string $filename Name of file that will hold the CSV results
  * @param array $config Array of configuration settings (currently unused)
  * @return string JSON-encoded string representing input data
  *
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    $json = json_encode($data, JSON_NUMERIC_CHECK);
    return $json;
  }

}