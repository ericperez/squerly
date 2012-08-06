<?php


$script = array_pop(explode('/',$_SERVER['PHP_SELF']));
foreach(glob(dirname(__FILE__) . '/*kml_*.php') as $file) if ($file != $script) include_once($file);



