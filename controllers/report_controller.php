<?php
/**
  *
  * Squerly - Report Controller
  * 
  * This file contains all the additional routes and supporting code that is specific to reports
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */


class Report_Controller extends Crud_Controller {

  //TODO: implement this
  protected static $_forms = array('add' => 'Form_Report_Add');

 /**
  *
  * Load a report
  *
  * @param int $id Report ID to load
  * @return object Report object from the report factory based on 'type' field
  *
  */
  protected static function _loadReport($id) {
    $id = is_int($id) ? $id : (int) F3::get('PARAMS["id"]') ?: null;
    if(!$id) { F3::reroute(F3::get('URL_BASE_PATH') . '/report'); }
    $report = Report::delegate($id);
    if($report->dry()) { F3::reroute(F3::get('URL_BASE_PATH') . '/report'); }
    return $report;
  }


 /**
  *
  * Email report action
  *
  * @param int $id Report ID to load
  *
  */
  public static function email($id = null) {
    $report = self::_loadReport($id);
    //TODO: 
    //Load a saved configuration
    //Validate form
    //Get the report results
    //Send out the email

  }


 /**
  *
  * 'Form Action'
  * 
  * Renders an HTML form for the given report
  *
  * @param int $id Report ID to load
  * 
  * @todo Finish this
  *
  */
  public static function form($id = null) {
    $report = self::_loadReport($id);
    //TODO: 
    //Load a saved configuration
    //Validate form
    //Get the report results
    //Send out the email

  }


 /**
  *
  * Render report results/output (AJAX) action
  * 
  * In contrast to 'results,' this action will load any front-end plugins necessary to render the report 
  *   results; if no render template is available, this method will returns the same data as 'results'
  *
  * @param int $id Report ID to load
  *
  */
  public static function render($id = null) {
    session_write_close(); //Open sessions will block concurrent requests
    $report = self::_loadReport($id);
    //TODO: run form validation and spit out messages on failure
    //Load the data from the data source and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    echo Export::loadLayout(Export::render($report->getResults(), $filename));
  }


 /**
  *
  * Report results/output (AJAX) action
  *
  * @param int $id Report ID to load
  *
  */
  public static function results($id = null) {
    session_write_close(); //Open sessions will block concurrent requests
    $report = self::_loadReport($id);
    //TODO: run form validation and spit out messages on failure
    //Load the data from the data source and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    echo Export::render($report->getResults(), $filename);
  }


 /**
  *
  * Run report action
  *
  * @param int $id Report ID to load
  *
  */
  public static function run($id = null) {
    $report = self::_loadReport($id);
    //Get the template tags out of the report query and input URI
    //TODO: make this an array with report field names as keys??

    //Parse out the mustache tags
    //Build a form from tags
    //Send form to view
  }


 /**
  *
  * Report validation (AJAX) action
  *
  * @param int $id Report ID to load
  *
  */
  public static function validate($id = null) {
    session_write_close(); //Open sessions will block concurrent requests
    //Load the report
    //TODO: run form validation and spit out messages on failure

    //Run the report against the DB and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    echo Export::render($report->getResults(), $filename);
  }

}

//Report Routes
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/email/@id', 'Report_Controller::email');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/form/@id', 'Report_Controller::form');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/render/@id', 'Report_Controller::render');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/run/@id', 'Report_Controller::run');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/validate/@id', 'Report_Controller::validate');
