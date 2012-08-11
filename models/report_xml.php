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
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
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
   * _preprocessQuery - preprocess in PHP
   * @param $preview boolean - If TRUE, limits the number of rows in the report results to self::REPORT_PREVIEW_ROWS
   *
   */
  protected function _preprocessQuery($preview) {
    $this->_phpPreprocess(); // Run the query through PHP
  }


  /**
   *
   * _postprocessResults - runs the results of the report query through any necessary post-processing
   *
   */
  protected function _postprocessResults() {
    $postprocess_code = String::stripComments($this->postprocess_code);
    if(!empty($postprocess_code)) { $this->_phpPostprocess(); }
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
    $this->results = Data_Source::loadXMLFile($this->input_data_uri, $max_rows);
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
