<?php
/**
  *
  * Squerly - CSV-based report class
  * 
  * Report_Csv is used to load data from a CSV file/URI and use it within the rest of the reporting framework
  * 
  * You should not instantiate this class directly; instead instantiate class 'Report' which has the ability
  * to delegate to/factory the proper report sub-class based on the 'type' property
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_Csv extends Report_Base {
  public $bind_params = array();
  const REPORT_PREVIEW_ROWS = 10;

  /**
   *
   * _isValid - checks report validity
   * @return boolean - True is valid, False if invalid
   *
   */
  protected function _isValid()
  {
    //TODO: this does not work for http URIs
    //if(is_readable($this->input_data_uri) && file_exists($this->input_data_uri)) { 
      return true; 
    //} else {
    //  F3::error('', "Invalid CSV input or file does not exist: {$this->input_data_uri}."); //TODO: make class constant
    //}
    //TODO: check for valid CSV input
  }


  /**
   *
   * _preprocessQuery - preprocess in PHP
   * @param $preview boolean - If TRUE, limits the number of rows in the report results to self::REPORT_PREVIEW_ROWS
   * @todo get $preview working
   *
   */
  protected function _preprocessQuery($preview, array $template_vals = array()) {
    $this->_phpPreprocess(); // Run the query through PHP
    //Use (sanitized) $_GET as bind-parameters unless overridden in $bind_params
    $template_vals = (empty($template_vals)) ? F3::get('REQUEST') : $template_vals;
    //Swap out the mustache/template tags with bind-parameter placeholders and gets an array of bind parameters/values
    list($this->processed_query, $this->bind_params) = Mustache_Helper::renderSQL($this->processed_query, $template_vals, '{[', ']}');

    //D3Linq doesn't support 'bind parameters' so instead of keeping them separate, they are going to be replaced into the query template
    $this->processed_query = Mustache_Helper::render($this->processed_query, array_map('addslashes', $this->bind_params));

    //Replace any template vars/tags in the report input_data_uri property
    $this->input_data_uri = Mustache_Helper::render($this->input_data_uri, $template_vals, 'rawurlencode');

  }


  /**
   *
   * getResults - Runs the report query against the database and returns the results
   *
   */
  public function getResults($preview = false) {
    $this->_preprocessQuery($preview); //Pre-process the query through various filters
    if($this->_isValid())
    {
      try {
        $this->getData($preview);
      }
      catch(Exception $e) {
        //TODO: Handle exception - display error details in development; generic error message in production
        throw new Exception($e);
      }
    }
    $this->_postprocessResults();
    return $this->results;
  }


  /**
   *
   * getColumns - Returns the column names for a given report
   *
   */
  public function getColumns() {
    //TODO: Implement
  }


  /**
   *
   * getData - Retrieves the initial results data from the data source
   *
   */
  public function getData($preview = false) {
    $max_rows = ($preview) ? self::REPORT_PREVIEW_ROWS : 0;
    //Run the data through D3Linq which semantically parses 'SQL' and applies it to 2D data
    //TODO: abstract this code out and add it to JSON and XML reports
    if($this->processed_query !== '') {
      $GLOBALS['data'] = Data_Source::loadCSVFile($this->input_data_uri, $max_rows);
      $this->results = array();
      $linq = new D3Linq();
      $linq->Query($this->processed_query);
      while($row = $linq->fetch_assoc()){
        $this->results[] = $row;
      }
    } else {
      $this->results = Data_Source::loadCSVFile($this->input_data_uri, $max_rows);
    }
  }


  /**
   *
   * getFormConfig - Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {
    //@todo: Implement
  }

}
