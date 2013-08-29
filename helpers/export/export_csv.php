<?php
/**
  *
  * Squerly - CSV file export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Csv implements Export_Interface {

/**
  *
  * Renders 2D associative array data as a CSV file
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename Name of file that will hold the CSV results
  * @param array $config Array of configuration settings (currently unused)
  * @return string comma-separated values representing input data
  *
  */
  public static function render(array $data, $filename = 'export', $config = array()) {
    $file = fopen('php://temp/maxmemory:'. (64*1024*1024), 'r+'); //64MB max before swap

    //Output the header row
    $header = array_keys($data[0]);
    fputcsv($file, $header, ',', '"');

    //Output the report results
    foreach($data as $data_row) {
      fputcsv($file, $data_row, ',', '"');
    }
    rewind($file);
    $output = stream_get_contents($file);
    fclose($file);

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
    return $output;
  }

}
