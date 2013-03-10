<?php
/**
  *
  * Squerly - Saved Report Configuration Controller
  * 
  * This file contains all the additional routes and supporting code that is specific to saved report configurations
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */


class Saved_Report_Controller extends Crud_Controller {
  public static $_model = 'saved_report';

 /**
  *
  * AJAX action/method to output serialized form values for a given saved report configuration
  * 
  * @param int $id Saved Report Configuration ID to load
  * @return string Serailized representation of a report input form
  *
  */
  public static function getValues($id = null) {
    $id = $id ?: (int) F3::get('PARAMS.id') ?: null;
    if($id === null) { return; }
    $saved_report_model = new Saved_Report('saved_report');
    $saved_report = $saved_report_model->load("id={$id}");
    if($saved_report === false) { return; }
    echo $saved_report->input_values ?: '';
    return;
  }


 /**
  *
  * 'List Records/Index' action
  *   
  */
  public static function index() {
    F3::set('PARAMS.model', self::$_model); //TODO: put this in a better place
    //These are the fields that show up on the index page
    $index_fields = 'id, name, enabled, report_id, created_at, updated_at';
    self::_getIndexRecords($index_fields);
    parent::index();
  }


 /**
  *
  * Load action will load a report with inputs populated by a saved report configuration and render the results
  * 
  * @param int $id Saved Report Configuration ID to load
  * @return string Serailized representation of a report input form
  *
  */
  public static function load($id = null) {
    session_write_close();
    $id = $id ?: (int) F3::get('PARAMS.id') ?: null;
    if($id === null) { return; } //TODO: something else besides just return
    $saved_report_model = new Saved_Report('saved_report');
    $saved_report = $saved_report_model->load("id={$id}");
    if($saved_report === false) { return; }
    $config = $saved_report->getConfig();
    $report = Report::load_model($saved_report->report_id);
    $report_results = Export::render($saved_report->getResults(), '', $config['context']);
    F3::set('report_results', $report_results);
    F3::set('page_title', $report->name);
    $form_values = $saved_report->getInputValues();
    F3::set('form', report_controller::renderParamsForm($report, 'render', $form_values));
    echo Export::loadLayout($config['context']);
  }

}
