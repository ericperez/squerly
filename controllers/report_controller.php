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

  //Report results/output (AJAX) action
  public static function results($id = null) {
    session_write_close(); //Open sessions will block concurrent requests
    $id = is_int($id) ? $id : (int) F3::get('PARAMS["id"]');
    $report = Report::delegate($id);
    //TODO: run form validation and spit out messages on failure

    //Run the report against the DB and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    echo Export::render($report->getResults(), $filename);
  }


  //Run report action
  public static function run() {
    //Load the report
    //TODO:
    //Load Form
    //Parse out the mustache tags
    //Build a form from tags
    //Send form to view
  }


  //Email report action
  public static function email() {
    //Load the report
    //TODO: 
    //Load a saved configuration
    //Validate form
    //Get the report results
    //Send out the email

  }

  //Report validation (AJAX) action
  public static function validate() {
    session_write_close(); //Open sessions will block concurrent requests
    //Load the report
    //TODO: run form validation and spit out messages on failure

    //Run the report against the DB and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    echo Export::render($report->getResults(), $filename);
  }

}

//Report Routes
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/run/@id', 'Report_Controller::run');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/email/@id', 'Report_Controller::email');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/validate/@id', 'Report_Controller::validate');

