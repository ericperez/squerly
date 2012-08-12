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
  * Finds the appropriate export/rendering plugin and calls it with the input 2D data array
  * 
  * @param array $data 2D array of data to be exported
  * @param string $filename Name of file to be sent back to the user (if applicable)
  * @param string $output_format Determines which export helper gets called (defaults to HTML table)
  * @param array $config Array of configuration settings
  *
  */
  public static function render($data, $filename = 'download.txt', $output_format = '', array $config = array()) {
    if(!$data) { $data = array(array()); }
    //Read output format/context from $output_format or $_GET
    $output_param = isset($_GET['context']) ? String::modelToClass($_GET['context']) : 'table';
    $output_format = (!empty($output_format)) ? $output_format : $output_param;
  
    //find the appropriate export plugin; return the rendered results
    $export_plugin = 'Export_' . $output_format;
    $class_implements = @class_implements($export_plugin) ?: array(); //Get plugin interfaces
    if(@class_exists($export_plugin) && in_array('Export_Interface', $class_implements)) {
      return $export_plugin::render($data, $filename, $config);
    } else {
      F3::error('', 'Invalid export plugin or plugin not found.');
    }
  }

}