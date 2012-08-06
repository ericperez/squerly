<?php
//Renders 2D associative array as a basic HTML table
class Export_Table implements Export_Interface {

  public static function render(array $data, $filename = NULL, $config = array()) {
    //Build the header
    $header_ids = array_keys($data[0]);
    $header = isset($data[0]) ? array_map('String::humanize', $header_ids) : array();
    $table = "<table><thead><tr>";
    foreach($header as $header_value)
    {
      $table .= "<th>{$header_value}</th>";
    }
    $table .= "</tr></thead>\n";

    //Build the table 
    foreach($data as $row)
    {
      $table .= '<tr>';
      foreach($row as $cell) {
        $table .= "<td>{$cell}</td>";
      }
      $table .= "</tr>\n";
    }
    $table .= "</table>\n";
    return $table;
  }

}