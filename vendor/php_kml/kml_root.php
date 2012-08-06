<?php
/**
 * This is where the rendering and dumping happens
 *
 * $Id$
 */

include_once('kml__init.php');

class kml_root {

    function kml_root() {}


   /* Render */
    function render($doc) {
        return $doc->createElement($this->tagName);
    }


    /* The Only Dump Function */
    function dump($header = true, $filename = false, $format = true) {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = $format;
        $root = $doc->appendChild($doc->createElement('kml'));
        $root->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');

        $root->appendChild($this->render($doc));
        
        $output = $doc->saveXml();
                
        if ($header) {
        	header('Content-type: application/vnd.google-earth.kml+xml; charset=UTF-8');
        	
        }
        
        if ($filename) header('Content-Disposition: attachment; filename="'.$filename.'.kml"');
        echo str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="iso-8859-1"?>', $output);
    }
}


