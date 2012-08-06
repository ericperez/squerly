<?php
//TODO namespace Squerly;

class Report_Test extends Report_Base
{

  /**
   *
   * _isValid - checks report SQL for disallowed/descructive keywords
   * @note - This is not completely comprehensive but should act as a basic sanity check for disallowed statments running
   *
   */
  protected function _isValid() {
    return true;
  }


  /**
   *
   * _preprocessQuery - preprocess in PHP, strips off comments, removes semi-colons, adds identifier comment to report SQL
   * @param $preview boolean - If TRUE, limits the number of rows in the report results to self::REPORT_PREVIEW_ROWS
   *
   */
  protected function _preprocessQuery($preview) {
  }


  /**
   *
   * _postprocessQuery - runs the results of the report query through any necessary post-processing
   *
   */
  protected function _postprocessResults() {
    $postprocess_code = String::stripComments($this->fields->postprocess_code);
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
      $this->getData();
    }
    $this->_postprocessResults();
    return $this->fields->results;
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
   * getFormConfig - Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {
    //@todo: Implement
  }


  /**
   *
   * getData - Retrieves the initial results data from the data source
   *
   */
  public function getData() {
    $this->fields->results = Data_Source::loadCSVFile('/Users/eperez/Downloads/text_expenses.csv');
  }

}