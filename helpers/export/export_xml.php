<?php
/**
  *
  * Squerly - XML file export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Xml implements Export_Interface {

/**
  *
  * Renders 2D array in XML format
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename Name of file that will hold the XML results
  * @param array $config Array of configuration settings (currently unused)
  * @return string XML-encoded representation of input data
  *
  * @todo Allow encoding to be passed in; allow DOM root/child node names to be configured
  * @todo Update render code to use the $config var for customization
  * 
  * @todo Set 'download' headers if filename is not null
  * @todo Apparently saveXML doesn't generate CDATA blocks!?!
  * 
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    $xml_obj = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><records />");
    foreach ($data as $row) {
      $row_obj = $xml_obj->addChild('row');
      foreach($row as $key => $val) {
        $key = String::machine((string) $key, true) ?: 'NULL';
        if(is_numeric(substr($key, 0, 1))) { $key = 'x' . $key; }
        $val = htmlspecialchars($val);
        $row_obj->addChild($key, $val);
      }
    }
    header ("Content-Type:text/xml");
    return !empty($filename) ? $xml_obj->saveXML() : $xml_obj->saveXML($filename); 
  }

}
