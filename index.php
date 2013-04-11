<?php
/**
  *
  * Squerly - Index
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */

//Check to make sure PHP version 5.3.7 or higher is being used; bail if not
if(version_compare(phpversion(), '5.3.7', '<')) { die('ERROR: Squerly requires PHP 5.3.7 or higher to run.'); }

require __DIR__ . '/lib/base.php'; //Fat-Free Framework (F3) core code
require __DIR__ . '/config/squerly.config.php'; //Squerly configuration settings
require __DIR__ . '/vendor/autoload.php'; //Composer package management/autoloading

//Fat-Free Framework vars
F3::set('TEMP', 'tmp/');
F3::set('IMPORTS', 'views/');
F3::set('UI', 'views/'); //Path to UI/Views
F3::set('AUTOLOAD', '
  lib/,
  models/,
  controllers/,
  helpers/,
  helpers/export/,
  helpers/transform/,
  forms/,
  vendor/,
  vendor/php_kml/,
  vendor/phpseclib/,
  vendor/PHPLinq/
');

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

//Array of data models that may be accessed through the CRUD routing interface
//Format: 'Friendly Name' => 'model name'
F3::set('CRUD_TABLE_WHITELIST', array(
  'Report' => 'report',
  'Saved Report Configuration' => 'saved_report',
  'Email Distribution List' => 'email_distribution_list',
  'Email Schedule' => 'email_schedule',
  //TODO: 'Event Schedule' => 'event_schedule',
));

Report_DB_Connection::loadAll(); //Load all the reporting database connections
Route::loadAll();

//Determine if a simple bootstrap is required or a full application load
if(!isset($bootstrap_only) || (isset($bootstrap_only) && !$bootstrap_only)) { 
  Crud_Controller::init();
  session_start();
  F3::run();
}
