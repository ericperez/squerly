<?php
include_once('kml_Geometry.php');


class kml_MultiGeometry extends kml_Geometry {

    var $tagName = 'MultiGeometry';

    var $Geometries;

    /* Constructor */
    function kml_MultiGeometry() {
        parent::kml_Geometry();
    }


    /* Assignments */
    function add_Geometry($Geometry) { $this->Geometries[] = $Geometry; }


   /* Render */
    function render($doc) {
        $X = parent::render($doc);

        if (isset($this->Geometries))
        {
            foreach($this->Geometries as $Geometry)
                $X->appendChild($Geometry->render($doc));
        }

        return $X;
    }
}


/*
$a = new kml_MultiGeometry(array(array(3, 4), array(3, 5)));
$a->dump(true);
*/

