<?php
/**
  *
  * Squerly - FLOT JSON export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
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
    $column_num = 0;
    $column_names = array_keys($data[0]);
    $output = array();
    $row = 0;
    foreach($column_names as $col_name) {
      // First column contains the X-Axis labels
      if($column_num === 0) { 
        $categories = Matrix::pick($data, $col_name);
        $column_num++; 
        continue; 
      }
      //Subsequent columns contain 'data series' for the chart
      $output[] = array('label' => String::humanize($col_name), 'data' => array());
      $row_data = array_map(function($in) { return (float) preg_replace('/[^0-9,\.\-e]/', '', $in); }, Matrix::pick($data, $col_name));
      foreach($row_data as $k => $row_val) {
        $category = preg_replace('/[^0-9,\.]/', '', $categories[$k]);
        $output[$row]['data'][] = array($category, $row_val);
      }
      $row++;
    }
    return json_encode($output);
  }

}