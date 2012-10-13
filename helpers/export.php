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
  * Uses $output_format if passed in, otherwise looks at $_GET['context'], and lastly
  *   defaults to 'table' (HTML Table) output format
  * 
  * @param string $output_format Output type name
  * @return string Name of output format
  * 
  */
  public static function getOutputFormat($output_format = '') {
      //Read output format/context from $output_format or $_GET
      $output_param = isset($_GET['context']) ? String::modelToClass($_GET['context']) : 'table';
      $output_format = (!empty($output_format)) ? $output_format : $output_param;
      return $output_format ?: 'table';
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


/**
  *
  * Renders an export plugin using an HTML layout
  * 
  * The output of this method can be used to build dashboards as it includes necessary front-end
  *   components to fully render the report results in a browser
  * 
  * @param string $content Export content to be put into the layout as 'content'
  *
  */
  public static function loadLayout($content, $page_title = '') {
    F3::set('content', $content);
    F3::set('page_title', $page_title);
    $output_format = self::getOutputFormat();
    $layout_path = 'export/' . $output_format . '.phtml';
    //TODO: make sure file exists
    return Template::serve($layout_path);
  }


}