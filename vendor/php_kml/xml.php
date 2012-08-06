<?php

function XML_create_text_element($doc, $tagName, $content = null, $attributes = array())
{
    $e = $doc->createElement($tagName);
    if ($content !== null) $e->appendChild($doc->createCDataSection($content));
    foreach($attributes as $k => $v) $e->setAttribute($k, $v);
    return $e;
}
