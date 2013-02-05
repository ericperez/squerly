<?php
/**
  *
  * Squerly - XML-based report class
  * 
  * Report_Xml is used to load data from an XML file/URI and use it within the rest of the reporting framework
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
class Report_Xml extends Report_Base {

  /**
   *
   * _isValid - checks report validity
   * @return boolean - True is valid, False if invalid
   *
   */
  protected function _isValid()
  {
    //if(is_readable($this->input_data_uri) && file_exists($this->input_data_uri)) { 
      return true; 
    //} else {
    //  F3::error('', "Invalid XML input or file does not exist: {$this->input_data_uri}."); //TODO: make class constant
    //}
    //TODO: check for valid XML input
  }


  /**
   *
   * Preprocess in PHP
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  protected function _preprocessQuery($max_return_rows = 0, $input_values = array()) {
    $this->_phpPreprocess(); // Run the query through PHP
    //Replace any template vars/tags in the report input_data_uri property
    $input_values = empty($input_values) ? $_REQUEST : $input_values;
    $this->input_data_uri = Mustache_Helper::render($this->input_data_uri, $input_values, 'rawurlencode');
  }


  /**
   *
   * Runs the report query against the XML data source and returns the results
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  public function getResults($max_return_rows = 0, array $input_values = array()) {
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
    $this->_postprocessResults($max_return_rows);
    return $this->results;
  }


  /**
   *
   * Returns the column names for a given report
   *
   */
  public function getColumns() {
    //TODO: Implement
  }


  /**
   *
   * Retrieves the initial results data from the data source
   *
   */
  public function getData($max_return_rows = 0) {
    $this->results = Data_Source::loadXMLFile($this->input_data_uri, $max_return_rows);
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
