<?php
//TODO: add namespace

class Export_Csv implements Export_Interface {

  //Renders report results as a CSV file
  public static function render(array $report_data, $filename = 'export', $config = array()) {
    $file = fopen('php://temp/maxmemory:'. (32*1024*1024), 'r+'); //32MB max before swap

    //Output the header row
    $header = array_keys($report_data[0]);
    fputcsv($file, $header, ',', '"');

    //Output the report results
    foreach($report_data as $data_row) {
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