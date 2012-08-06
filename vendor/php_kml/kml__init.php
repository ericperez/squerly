<?php
error_reporting(E_ALL);
include_once('xml.php');

function color2kml($color, $alpha = 'FF') {
    return sprintf("$alpha%s%s%s", 
		substr($color,4,2), 
		substr($color,2,2), 
		substr($color,0,2));
}





/**** definitions ****/

// altitudeModeEnum <LookAt> & <Region>
define('KML_clampToGround',     'clampToGround');
define('KML_relativeToGround', 'relativeToGround');
define('KML_absolute',          'absolute ');

// colorModeEnum <ColorStyle>
define('KML_normal', 'normal');
define('KML_random', 'random');

// refreshModeEnum <Link>
define('KML_onChange',   'onChange');
define('KML_onInterval', 'onInterval');
define('KML_onExpire',   'onExpire');

// viewRefreshEnum <Link>
define('KML_never',     'never');
define('KML_onStop',    'onStop');
define('KML_onRequest', 'onRequest');
define('KML_onRegion',  'onRegion');

// listItemTypeEnum <ListStyle>
define('KML_check',             'check');
define('KML_radioFolder',       'radioFolder');
define('KML_checkOffOnly',      'checkOffOnly');
define('KML_checkHideChildren', 'checkHideChildren');

// styleStateEnum <StyleMap>
//define('KML_normal',    'normal');
define('KML_highlight', 'highlight');

// unitsEnum See <hotSpot> in <IconStyle>, <ScreenOverlay>
define('KML_fraction',    'fraction');
define('KML_pixels',      'pixels');
define('KML_insetPixels', 'insetPixels');


// itemIconModeEnum
define('KML_open',      'open');
define('KML_closed',    'closed');
define('KML_error',     'error');
define('KML_fetching0', 'fetching0');
define('KML_fetching1', 'fetching1');
define('KML_fetching2', 'fetching2');

/*

//
define('KML_', '');

*/
