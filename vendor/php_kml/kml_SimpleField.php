<?php
include_once('kml_SchemaField.php');


class kml_SimpleField extends kml_SchemaField {

    var $tagName = 'SimpleField';


    /* Constructor */
    function kml_SimpleField($name = null, $type = null) {
        parent::kml_SchemaField();
        if ($name !== null) $this->name($name);
        if ($type !== null) $this->type($type);

    }


    /* Render */
    function render($doc) {
        $X = parent::render($doc);
        return $X;
    }

}

/**
$a = new kml_SimpleField('iiii');
$a->dump(false);
*/
