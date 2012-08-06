<?php
include_once('kml_SchemaField.php');


class kml_ObjField extends kml_SchemaField {

    var $tagName = 'ObjField';


    /* Constructor */
    function kml_ObjField($name = null, $type = null) {
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
$a = new kml_ObjField('iiii');
$a->dump(false);
*/
