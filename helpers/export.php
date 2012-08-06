<?php
class Export {

  //Farm out converting various data formats into a 2D array
  public static function render($data, $filename = 'download.txt', $output_format = '') {
    if(!$data) { $data = array(array()); }
    //Read output format/context from $output_format or $_GET
    $output_param = isset($_GET['context']) ? String::modelToClass($_GET['context']) : 'table';
    $output_format = (!empty($output_format)) ? $output_format : $output_param;
  
    //find the appropriate export plugin; return the rendered results
    $export_plugin = 'Export_' . $output_format;
    $class_implements = @class_implements($export_plugin) ?: array();
    if(@class_exists($export_plugin) && in_array('Export_Interface', $class_implements)) {
      return $export_plugin::render($data, $filename);
    } else {
      F3::error('', 'Invalid export plugin or plugin not found.');
    }
  }

}