<?php
/**
  *
  * Squerly - PHP code-based report class
  * 
  * Report_Php is used to load data using arbitrary PHP code. As long as the 'getResults' method returns a 2D array
  * the rest of the framework can be used to output/render the results, etc. Using the PHP Report API, it is possible
  * to use this class to merge the results of other reports together or do any sort of custom coding to retrieve data
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
class Report_Php extends Report_Base {

  /**
   *
   * _postprocessQuery - runs the results of the report query through any necessary post-processing
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
    $this->_postprocessResults();
    return $this->results;
  }


  /**
   *
   * getColumns - Returns the column names for a given report
   *
   */
  public function getColumns() {
    //@todo: Implement
  }


  /**
   *
   * getData - Stub
   * 
   * @note A 2D Array should be returned by PHP code that is implemented in the report->postprocess_code property
   *
   */
  public function getData() { }


  /**
   *
   * getFormConfig - Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {
    //@todo: Implement
  }

}