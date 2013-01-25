<?php
/**
  *
  * Squerly - Transformation class to calculate instant velocity of values in a 2D array
  * 
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Transform_Cumulative_Sum implements Transform_Interface {

/**
  *
  * Calculates the cumulative sum for each item in a one-dimensional numeric data array
  * 
  * @param array $y_values array of data to be transformed
  * @return array Input array after transformation
  *
  */
  protected static function cumulative_sums(array $y_values) {
    $output = array();
    foreach($y_values as $k => &$v) {
      $output[$k] = array_sum(array_filter(array_slice($y_values, 0, $k + 1)));
    }
    return $output;
  }


/**
  *
  * Calculates the cumulative sum for each series in a two-dimensional data array
  * 
  * @param array $data 2D associative array of data to be transformed
  * @param array $fields Array of fields to apply the tranformation to (defaults to all fields)
  * @return array $data Array after transformation
  *
  * @todo Implement $fields limitation
  * 
  */
  public static function run(array $data, array $fields = array()) {
    $output = array();
    $col_names = array_keys($data[0]);
    $transpose = Matrix::transpose($data);
    $row_num = 0;
    foreach($transpose as $k => $row) {
      // First row contains the Y-Axis labels
      $column = $col_names[$row_num];
      if($row_num === 0) { 
        $output[$column] = $row;
        $row_num++; 
        continue;
      }
      //Subsequent rows contain y-values
      $output[$column] = self::cumulative_sums($row);
      $row_num++;
     }
     return Matrix::transpose($output);
  }

}

