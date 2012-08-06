<?php
include_once('kml_Object.php');


class kml_StyleSelector extends kml_Object {

    var $tagName = 'StyleSelector';


    /* Constructor */
    function kml_StyleSelector() {
        parent::kml_Object();
    }


    /* Render */
    function render($doc) {
        $X = parent::render($doc);
        return $X;
    }
}

/*
$a = new kml_StyleSelector();
$a->dump(false);
*/