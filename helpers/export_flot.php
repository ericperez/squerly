<?php
//Renders 2D array as a FLOT JSON string
//Format: [ {data:[[x,y]], label:"Label"} ]
class Export_Flot implements Export_Interface {

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