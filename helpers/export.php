<?php
/**
  *
  * Squerly - Main data export class
  * 
  * This class farms out converting 2D array data into various formats to a number of plugins.
  * The export sub-classes/plugins must implement 'Export_Interface' and
  *   be named 'Export_' followed by a short description.
  * @see See the existing export classes under '/helpers/Export_*.php' for examples of how this works
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export {

 /**
  *
  * Attempts to determine the output format the user requested
  * 
  * Uses $output_format if passed in, otherwise looks at $_REQUEST['sqrl']['context'], and lastly
  *   defaults to 'Table' (HTML Table) output format
  * 
  * @param string $output_format Output type name
  * @return string Name of output format
  * 
  */
  public static function getOutputFormat($output_format = '') {
    $request_format = isset($_REQUEST['sqrl']['context']) ? $_REQUEST['sqrl']['context'] : '';
    return $output_format ?: String::modelToClass($request_format) ?: 'Table';
  }


/**
  *
  * Renders an export plugin using an HTML layout
  * 
  * The output of this method can be used to build dashboards as it includes necessary front-end
  *   components to fully render the report results in a browser
  * 
  * @return string|boolean HTML markup if layout exists; boolean false if it doesn't
  *
  */
  public static function loadLayout() {
    $output_format = self::getOutputFormat();
    $layout_path = 'export/' . $output_format . '.phtml';
    $full_path = __DIR__ . '/../views/' . $layout_path;
    if(file_exists($full_path)) {
      return Template::serve($layout_path, null);
    } else {
      return false;
    }
  }


 /**
  *
  * Enumerates all the export plugins and returns an an array of their names
  * 
  * @return array of export plugins in format 'short_name' => 'friendly name'
  * 
  */
  public static function pairs() {
    //TODO: Automate this...
    return array(
      'table' => 'HTML Table',
      'highcharts' => 'Highcharts Line Graph',
      'flot' => 'FLOT Line Graph',
      'pchart' => 'PChart Line Graph',
      'csv' => 'CSV',
      'json' => 'JSON',
      'xml' => 'XML',
      'kml_points' => 'KML Points',
    );
  }


 /**
  *
  * Calls a rendering plugin with the input 2D data array
  * 
  * @param array $data 2D array of data to be exported
  * @param string $filename Name of file to be sent back to the user (if applicable)
  * @param string $output_format Determines which export helper gets called (defaults to HTML table)
  * @param array $config Array of configuration settings
  *
  */
  public static function render($data, $filename = 'download.txt', $output_format = '', array $config = array()) {
    if(!$data) { $data = array(array()); }
    $export_plugin = 'Export_' . self::getOutputFormat($output_format);
    $class_implements = @class_implements($export_plugin) ?: array(); //Get plugin interfaces
    if(@class_exists($export_plugin) && in_array('Export_Interface', $class_implements)) {
      return $export_plugin::render($data, $filename, $config);
    } else {
      F3::error('', 'Invalid export plugin or plugin not found.');
    }
  }


}