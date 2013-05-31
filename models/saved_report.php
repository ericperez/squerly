<?php
/**
  *
  * Squerly - Saved Report Form Configurations Model
  * 
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Saved_Report extends CRUD {

 /**
  *
  * Returns an associative array of configuration values for this saved report
  * 
  * @return array Saved Report Configuration configuration values
  *
  */
  public function getConfig() {
    $saved_report_values = $this->getInputValues();
    $config_keys = array('max_return_rows' => 0, 'transform' => null, 'context' => null); //TODO: pull these from a unified place
    return $saved_report_values['sqrl'] + $config_keys;
  }

 /**
  *
  * Returns an associative array of saved form values for a given report
  * 
  * @return array Saved Report Configuration form values
  *
  */
  public function getInputValues() {
    $output = array();
    parse_str($this->input_values, $output);
    return $output;
  }


 /**
  *
  * Runs the report against the data source using the saved report configuration input_values as inputs
  *
  * @return array Saved Report Configuration Results
  *
  */
  public function getResults() {
    $config = $this->getConfig();
    $saved_report_values = $this->getInputValues();
    $report = Report::load_model($this->report_id);
    return $report->getResults($config['max_return_rows'], $saved_report_values, $config['transform']);
  }

}
