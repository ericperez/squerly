<?php
include_once('kml_Object.php');


class kml_TimePrimitive extends kml_Object {

    var $tagName = 'TimePrimitive';


    /* Constructor */
    function kml_TimePrimitive() {
        parent::kml_Object();
    }


    /* Render */
    function render($doc) {
        $X = parent::render($doc);
        return $X;
    }
}

/*
$a = new kml_TimePrimitive();
$a->dump(false);
*/