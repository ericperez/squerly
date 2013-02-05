<?php
/**
  *
  * Squerly - Google Maps KML export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Kml_Points implements Export_Interface {

/**
  *
  * Renders 2D associative array in KML XML format with each row representing a point
  * 
  * Data exported to this format can be used as input for Google Maps to generate points on the map
  * The following input keys are used (and must be present): 'name', 'description', 'latitude', 'longitude'
  * @todo Add 'icon_color', 'icon_href', etc. as inputs for configuration
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename
  * @param array $config Array of configuration settings (currently unused)
  * @return string KML string representation of input data
  *
  * @todo Update render code to use the $config var for customization
  * @todo Document better the fact that 'name', 'description', etc. are required fields/keys in main docs/wiki
  * 
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    $kml = new kml_Document();
    $kml->set_id('uid' . mt_rand());

    $style = new kml_Style();
    $style->set_id('randomColorIcon');

    $icon = new kml_Icon('http://maps.google.com/mapfiles/kml/pal3/icon21.png');

    //Set up a random colored icon style
    $icon_style = new kml_IconStyle();
    $icon_style->set_scale(1.1);
    $icon_style->set_icon($icon);
    $icon_style->set_colorMode('random'); //TODO: fix this
    $icon_style->set_color('ffffffff');

    $style->set_IconStyle($icon_style);
    $kml->add_Feature($style);

    //TODO: document which fields are required for KML points exporting to work
    foreach($data as $row)
    {
      if(!isset($row['latitude'])  || $row['latitude'] === '' ||
         !isset($row['longitude']) || $row['longitude'] === '' ||
         !isset($row['name'])      || $row['name'] === ''
      ) { continue; }
      $placemark = new kml_Placemark($row['name'], new kml_Point($row['longitude'], $row['latitude']));
      if(isset($row['description'])) {
        $placemark->set_description($row['description']);
      }
      $placemark->set_styleUrl('#randomColorIcon');
      $kml->add_Feature($placemark);
    }
    return $kml->dump(true, $filename, true);
  }

}
