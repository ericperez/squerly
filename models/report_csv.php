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
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_Csv extends Report_Base {
  public $bind_params = array();

  /**
   *
   * Checks report/query validity
   * 
   * @return boolean True is valid, False if invalid
   * @todo Finish this
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
   *  Preprocess query with PHP
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  protected function _preprocessQuery($max_return_rows = 0, array $input_values = array()) {
    $this->_phpPreprocess(); // Run the query through PHP
    //Use $_REQUEST as bind-parameters unless overridden in $bind_params
    //TODO: figure out why F3::get('REQUEST') doesn't always work
    $input_values = (empty($input_values)) ? $_REQUEST : $input_values;
    //Swap out the mustache/template tags with bind-parameter placeholders and gets an array of bind parameters/values
    list($this->processed_query, $this->bind_params) = Mustache_Helper::renderSQL($this->processed_query, $input_values, '{[', ']}');

    //D3Linq doesn't support 'bind parameters' so instead of keeping them separate, they are going to be replaced into the query template
    $this->processed_query = Mustache_Helper::render($this->processed_query, array_map('addslashes', $this->bind_params));

    //Replace any template vars/tags in the report input_data_uri property
    $this->input_data_uri = Mustache_Helper::render($this->input_data_uri, $input_values, 'rawurlencode');

  }


  /**
   *
   * Runs the report query against the CSV data source and returns the results
   * 
   * @param integer $max_return_rows Maximum number of rows of data to be returned (0 is unlimited)
   * @param array $input_values Array of input key-value pairs to plug into the report query
   * @param string $transformation Data transformation to apply to report results
   *
   * @throws Exception if call to $this->getData fails
   *
   * @return Report results
   *
   */
  public function getResults($max_return_rows = 0, array $input_values = array(), $transformation = null) {
    $this->_preprocessQuery($max_return_rows, $input_values); //Pre-process the query through various filters
    if($this->_isValid())
    {
      try {
        $this->getData($max_return_rows);
      }
      catch(Exception $e) {
        //TODO: Handle exception - display error details in development; generic error message in production
        throw new Exception($e);
      }
    }
    $this->_postprocessResults($max_return_rows, $transformation);
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
   * Retrieves the initial results data from the data source
   * 
   * @todo Should this be public?
   *
   */
  public function getData($max_return_rows = 0) {
    //Run the data through D3Linq which semantically parses 'SQL' and applies it to 2D array data
    //TODO: abstract this code out and add it to JSON and XML reports
    if($this->processed_query !== '') {
      //Unfortunately the way that the D3Linq library is written, this data must go into $GLOBALS['data']
      $GLOBALS['data'] = Data_Source::loadCSVFile($this->input_data_uri, $max_return_rows);
      $this->results = array();
      $linq = new D3Linq();
      $linq->Query($this->processed_query);
      while($row = $linq->fetch_assoc()){
        $this->results[] = $row;
      }
    } else {
      $this->results = Data_Source::loadCSVFile($this->input_data_uri, $max_return_rows);
    }
  }


  /**
   *
   * Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {
    //@todo: Implement
  }

}
