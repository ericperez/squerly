<?php
/**
  *
  * Squerly - Index
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */


require __DIR__ . '/lib/base.php'; //Fat-Free Framework (F3) core code
require __DIR__ . '/config/squerly.config.php'; //Squerly configuration settings
require __DIR__ . '/models/crud.php'; 
//TODO: autoload these or 'require' in a loop
require __DIR__ . '/controllers/auth_controller.php';
require __DIR__ . '/controllers/report_controller.php';
require __DIR__ . '/vendor/depage-forms/htmlform.php'; //depage forms library

//Workaround for undefined 'gettext'/_() method (disables any non-default translations)
//@see http://www.php.net/manual/en/gettext.installation.php
//TODO: add 'extension=gettext.so' to php.ini
if(!function_exists('_')) {
  function _($input) {
    return $input;
  }
}

//Home Page route (currently reroutes to default model index)
F3::route('GET ' . F3::get('URL_BASE_PATH'),
  function() {
    list($model, $model_friendly) = CRUD_Helper::getModelName(true);
    F3::reroute(F3::get('URL_BASE_PATH') . $model);
  }
);

session_start();
Crud_Controller::setUpRoutes();
F3::run();
