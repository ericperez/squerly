<?php
//Renders 2D array in JSON format
class Export_Json implements Export_Interface {

  public static function render(array $data, $filename = NULL, $config = array()) {
    $json = json_encode($data, JSON_NUMERIC_CHECK);
    return $json;
  }

}