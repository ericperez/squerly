<?php
/**
  *
  * Squerly - Report Configuration Controller
  * 
  * This file contains all the additional routes and supporting code that is specific to report configurations
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */


class Report_Configuration_Controller extends Crud_Controller {

 /**
  *
  * AJAX action/method to output serialized form values for a give report configuration
  * 
  * @param int $id Report Configuration ID to load
  * @return string Serailized representation of a report input form
  *
  */
  public static function getValues($id = null) {
    $id = $id ?: (int) F3::get('PARAMS.id') ?: null;
    if($id === null) { return; }
    $report_config = new Report_Configuration('report_configuration');
    $report_config_model = $report_config->load("id={$id}");
    if($report_config_model === false) { return; }
    echo $report_config_model->input_values ?: '';
    return;
  }

}

F3::route('GET ' . F3::get('URL_BASE_PATH') . 'report_configuration/getvalues/@id', 'Report_Configuration_Controller::getValues', 10);
