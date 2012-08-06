<?php
//Renders 2D array in XML format
class Export_Xml implements Export_Interface {

  //TODO: allow encoding to be passed in; allow DOM root/child node names to be configured
  public static function render(array $data, $filename = NULL, $config = array()) {
    $xml_obj = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><records />");
    foreach ($data as $row) {
      $row_obj = $xml_obj->addChild('row');
      foreach($row as $key => $val) {
        $key = String::machine((string) $key, true) ?: 'NULL';
        $val = (string) $val;
        $row_obj->addChild($key, $val);
      }
    }
    //TODO: set download headers if filename set
    header ("Content-Type:text/xml");
    return !empty($filename) ? $xml_obj->saveXML() : $xml_obj->saveXML($filename); 
  }

}
