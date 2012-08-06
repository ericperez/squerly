<?php
//Renders 2D associative array as a KML file
//Utilizes the following keys: 'name', 'description', 'latitude', 'longitude', TODO: 'icon_color', 'icon_href'
//TODO: expand with more KML features
class Export_Kml_Points implements Export_Interface {

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

    foreach($data as $row)
    {
      if(!isset($row['latitude']) || $row['latitude'] === '' ||
        !isset($row['longitude']) || $row['longitude'] === '' ||
        !isset($row['name']) || $row['name'] === ''
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