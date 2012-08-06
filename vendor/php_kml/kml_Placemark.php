<?php
include_once('kml_Feature.php');


class kml_Placemark extends kml_Feature {

    var $tagName = 'Placemark';

    var $Geometries;

    /* Constructor */
    function kml_Placemark($name = null, $Geometry = null) {
        parent::kml_Feature($name);
        if ($Geometry !== null) $this->add_Geometry($Geometry);
    }

    /* Assignments */
    function add_Geometry($Geometry) { $this->Geometries[] = $Geometry; }

    /* Render */
    function render($doc) {
        $X = parent::render($doc);

        if (isset($this->Geometries))
            foreach($this->Geometries as $G)
                $X->appendChild($G->render($doc));

        return $X;
    }
}
