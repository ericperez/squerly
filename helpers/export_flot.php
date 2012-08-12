<?php
/**
  *
  * Squerly - FLOT JSON export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Flot implements Export_Interface {

/**
  *
  * Renders 2D array as a FLOT JSON string
  * 
  * Format: [ {data:[[x,y]], label:"Label"} ]
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename Unused for this data exporter
  * @param array $config Array of configuration settings (currently unused)
  * @return array string JSON string to load into FLOT for grahing
  * 
  * @todo Better detection of 'date' column/assume first one is always the date
  *
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    if(!isset($row['date'])) { return '[]'; }
    $output = array();
    $y = 0;
    foreach($data as $row) {
      $x = 0;
      foreach($row as $label => $value) {
        $date = date('U', strtotime($row['date'])) * 1000; ///assumes X is a date ??
        if($y == 0 && $x > 0) {
          $ouput[] = array("label" => $label, "data" => array());
        }
        if($x > 0) {
          $ouput[$x - 1]['data'][] = array($date, $value);
        }
        $x++;
      }
      $y++;
    }
    return json_encode($output);
  }

}