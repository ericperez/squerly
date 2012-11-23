<?php
/**
  *
  * Squerly - Transformation class to calculate minimum value across series in a 2D array
  * 
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Transform_Min implements Transform_Interface {


/**
  *
  * Find the minimum value across all series of a two-dimensional data array
  * 
  * @param array $data 2D associative array of data to be transformed
  * @param array $fields Array of fields to apply the tranformation to (defaults to all fields)
  * @return array $data Array after transformation
  *
  * @todo Implement $fields limitation
  * 
  */
  public static function run(array $data, array $fields = array()) {
    $col_names = array_keys($data[0]);
    $y_col = $col_names[0];
    $output = array();
    foreach($data as $row) {
      $vals = array_values($row);
      $y_val = array_shift($vals); //First column contains y-axis value
      $output[] = array($y_col => $y_val, 'MIN' => min($vals));
    }
    return $output;
  }

}

