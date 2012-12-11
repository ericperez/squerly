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
  protected static function _loadReport($id = null) {
    $id = is_int($id) ? $id : (int) F3::get('PARAMS["id"]') ?: null;
    if(!$id) { F3::reroute(F3::get('URL_BASE_PATH') . '/report'); }
    $report = Report::load_model($id);
    return $report;
  }


 /**
  *
  * Extracts all of the template vars out of a report and builds an HTML form
  *
  * @param object $report - Report object to render an input parameter form for
  * @param string $action - Report controller action the form action should point to
  * @return string HTML markup
  *
  * @todo Make all form attributes configurable
  * @todo Add date widgets to date fields, etc
  * 
  */
  protected static function _renderParamsForm($report, $action = 'render', array $form_vals = array()) {
    //TODO: loop through all properties for template/form vars instead of just query and input_data_uri?
    //TODO: Use form library to generate/validate form elements
    $vars = array_unique(Mustache_Helper::vars($report->clean_properties['query']) + Mustache_Helper::vars($report->clean_properties['input_data_uri']));
    $vals = !empty($form_vals) ? $form_vals : $_REQUEST ;
    //TODO: load form action from report property
    $form_html = "<div style='padding: 5px;'>" . Form::open("/report/{$action}/{$report->id}", array('method' => 'post'));
    //Currently all fields are required; TODO: make this configurable
    $input_attribs = array('required' => 'required');

    //Build the drop down for the saved report configurations
    $config_where = "report_id = " . F3::get('PARAMS.id');
    //TODO: make this smarter -- set table name in model
    $config_list = array('' => '(Select)') + Report_Configuration::pairs('report_configuration', false, $config_where, 'pkey DESC');
    $config_attribs = array(
      'title' => 'Load a Saved Report Configuration',
      'onchange' => 'squerly.report_configuration.getValues(this.value);',
    );

    //Build the drop down for the report rendering output formats
    $output_formats = Export::pairs();
    $output_val = isset($_REQUEST['sqrl']['context']) ? $_REQUEST['sqrl']['context'] : 'table';
    $output_attribs = array('title' => 'Report Output Format');

    //Build the drop down for the report output data transformations
    $transform = Transform::pairs();
    $transform_val = isset($_REQUEST['sqrl']['transform']) ? $_REQUEST['sqrl']['transform'] : '';
    $transform_attribs = array('title' => 'Apply a data transformation');

    //Save report config button attributes
    $save_config_attribs = array(
      'title' => 'Save the current report configuration',
      'onclick' => 'squerly.report_configuration.save();',
    );

    foreach($vars as $var) {
      $val = isset($vals[$var]) ? htmlentities($vals[$var]) : '';
      $form_html .= Form::label($var, String::humanize($var)) . ': ' . Form::input($var, $val, $input_attribs) . '&nbsp;';
    }

    $form_html .= '<br><br>';
    $form_html .= Form::label('sqrl[config]', 'Load a Saved Configuration:') . 
      Form::select('sqrl[config]', $config_list, '', $config_attribs) . '&nbsp;' .
      Form::label('sqrl[context]', 'Output Format:') . 
      Form::select('sqrl[context]', $output_formats, $output_val, $output_attribs) . '&nbsp;' .
      Form::label('sqrl[transform]', 'Data Transformation:') .
      Form::select('sqrl[transform]', $transform, $transform_val , $transform_attribs) . '&nbsp;' .
      Form::label('sqrl[preview]', 'Preview?') . 
      Form::checkbox('sqrl[preview]', '10') . '&nbsp;' .
      Form::button('sqrl[save_config]', 'Save Config', $save_config_attribs) .
      Form::submit('sqrl[run]', 'Run', array('value' => 'run', 'title' => 'Run the report and render the results')) .
      '<br>';

    $form_html  .= '</div>' . Form::close();
    return $form_html;
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
    $emailer = new SMTP();
    $emailer->set('From', 'test@squerly.net');
    $emailer->set('To', 'zerep@rocketmail.com');
    $emailer->set('Subject', 'Squerly Test');
    $emailer->set('Body', 'This is a Squerly test.');
    $emailer->send();

    //TODO: 
    //Load a saved configuration (or use $_REQUEST)
    //Validate form
    //Get the report results
    //Send out the email

  }


 /**
  *
  * 'Form Action'
  * 
  * AJAX action to render the input parameters form for a given report by ID
  *
  * @param int $id Report ID to load
  * @todo Update this to use the depage-forms library
  *
  */
  public static function form() {
    //TODO: Load a saved configuration
    //TODO: Validate form
    $report = self::_loadReport();
    echo self::_renderParamsForm($report);
  }


 /**
  *
  * 'List Records/Index' action
  * 
  * @param boolean $bogus - Unused param for inheritance sake
  *
  *   
  */
  public static function index($bogus = false) {
    //These are the fields that show up on the index page
    $index_fields = 'id, type, name, enabled, hidden_from_ui, created_at, updated_at';
    self::_getIndexRecords($index_fields);
    parent::index(false);
  }


 /**
  *
  * Load action
  *   
  */
  public static function load() {
    self::render(null, false);
  }


 /**
  *
  * 'HTML Select' Action - Echos ID/name value pairs for a given model as an HTML select element
  * 
  * This can be used in AJAX calls to populate the innerHTML of a DIV with the list of available model instances
  * 
  * @param array $config Form select element configuration
  * @param string $where unused
  * @param string $order_by unused
  * 
  * @todo: Allow config to be passed in or read from GET params
  *
  */
  public static function optionlist($config = null, $where = '', $order_by = '') {
    $hidden_from_ui_where = " (hidden_from_ui = 0 OR hidden_from_ui = 'false') ";
    $where = empty($where) ? $hidden_from_ui_where : $where . ' AND ' . $hidden_from_ui_where;
    parent::optionlist($config, $where, $order_by);
  }


 /**
  *
  * Render report results/output (AJAX) action
  * 
  * In contrast to 'results,' this action will load any front-end plugins necessary to render the report 
  *   results; if no render template is available, this method will returns the same data as 'results'
  * 
  * @param int $id Report ID to load
  * @param boolean $render_results Determines whether the report is run against the data source and the results rendered
  *
  */
  public static function render($id = null, $render_results = true) {
    session_write_close(); //Open sessions will block concurrent requests
    $report = self::_loadReport($id);
    //TODO: run form validation and spit out messages on failure
    //Load the data from the data source and render the results
    $filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    $max_return_rows = (isset($_REQUEST['sqrl']['preview'])) ? $_REQUEST['sqrl']['preview'] : 0;
    $report_results = ($render_results && strtolower(F3::get('POST.sqrl.run')) === 'run') ? 
      Export::render($report->getResults($max_return_rows)) : '';
    F3::set('report_results', $report_results);
    F3::set('page_title', $report->name);
    F3::set('form', self::_renderParamsForm($report));
    $layout = Export::loadLayout();
    if($layout === false) {
      self::results();
    } else {
      echo $layout;
    }
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
    $max_return_rows = (isset($_REQUEST['sqrl']['preview'])) ? $_REQUEST['sqrl']['preview'] : 0;
    echo Export::render($report->getResults($max_return_rows), $filename);
  }


 /**
  *
  * Run report action
  *
  * @param int $id Report ID to load
  * 
  * @todo Finish this
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
  * @todo Finish this
  *
  */
  public static function validate($id = null) {
    session_write_close(); //Open sessions will block concurrent requests
    //Load the report
    //TODO: run form validation and spit out messages on failure

    //Run the report against the DB and render the results
    //$filename = String::machine($report->name) . '_results_' . date('m-d-Y');
    //echo Export::render($report->getResults(), $filename);
  }

}


//Report Routes
//TODO: put these into a method
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/optionlist', 'Report_Controller::optionlist', 30);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/email/@id', 'Report_Controller::email');
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/form/@id', 'Report_Controller::form', 10);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/render/@id', 'Report_Controller::render', 120);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results', 120);
F3::route('POST ' . F3::get('URL_BASE_PATH') . 'report/render/@id', 'Report_Controller::render', 120);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/load/@id', 'Report_Controller::load', 120);
F3::route('POST ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results', 120);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/run/@id', 'Report_Controller::run', 120);
F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report/validate/@id', 'Report_Controller::validate');
