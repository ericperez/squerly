<?php
/**
  *
  * Squerly - HTML Table export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Table implements Export_Interface {

/**
  *
  * Renders 2D associative array as a basic HTML table
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename (unused)
  * @param array $config Array of configuration settings (currently unused)
  * @return string HTML Table representation of input data
  *
  * @todo Update render code to use the $config var for customization
  * 
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    //Build the header
    $header_ids = array_keys($data[0]);
    $header = isset($data[0]) ? array_map('String::humanize', $header_ids) : array();
    $table = "<table class='datatable'><thead><tr>";
    foreach($header as $header_value)
    {
      $table .= "<th>{$header_value}</th>";
    }
    $table .= "</tr></thead>\n";

    //Build the table 
    foreach($data as $row)
    {
      $table .= '<tr>';
      foreach($row as $cell_content) {
        $cell_content = trim($cell_content);
        if($cell_content === '') { $cell_content = '&nbsp;'; }
        $table .= "<td>{$cell_content}</td>";
      }
      $table .= "</tr>\n";
    }
    $table .= "</table>\n";
    return $table;
  }

}
