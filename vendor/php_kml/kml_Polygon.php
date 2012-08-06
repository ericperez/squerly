<?php
include_once('kml_Geometry.php');


class kml_Polygon extends kml_Geometry {

    var $tagName = 'Polygon';

    var $extrude;
    var $tesselate;
    var $altitudeMode;

    var $outerBoundaryIs;
    var $innerBoundaryIss;


    /* Constructor */
    function kml_Polygon($outerBoundaryIs) {
        parent::kml_Geometry();
        $this->set_outerBoundaryIs($outerBoundaryIs);
    }


    /* Assignments */
    function set_extrude($extrude) { $this->extrude = (int)$extrude; }
    function set_tesselate($tesselate) { $this->tesselate = (int)$tesselate; }
    function set_altitudeMode($altitudeMode) { $this->altitudeMode = $altitudeMode; }

    function set_outerBoundaryIs($outerBoundaryIs) { $this->outerBoundaryIs = $outerBoundaryIs; }
    function add_innerBoundaryIs($innerBoundaryIs) { $this->innerBoundaryIss[] = $innerBoundaryIs; }


   /* Render */
    function render($doc) {
        $X = parent::render($doc);

        if (isset($this->extrude)) $X->appendChild(XML_create_text_element($doc, 'extrude', $this->extrude));
        if (isset($this->tesselate)) $X->appendChild(XML_create_text_element($doc, 'tesselate', $this->tesselate));
        if (isset($this->altitudeMode)) $X->appendChild(XML_create_text_element($doc, 'altitudeMode', $this->altitudeMode));

        $b = $X->appendChild($doc->createElement('outerBoundaryIs'));
        $b->appendChild($this->outerBoundaryIs->render($doc));

        if (isset($this->innerBoundaryIss))
        {
            $b = $X->appendChild($doc->createElement('innerBoundaryIs'));
            foreach($this->innerBoundaryIss as $innerBoundaryIs)
                $b->appendChild($innerBoundaryIs->render($doc));
        }

        return $X;
    }
}


/*
$a = new kml_Polygon(array(array(3, 4), array(3, 5)));
$a->dump(true);
*/

