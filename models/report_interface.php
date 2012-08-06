<?php
//TODO namespace Squerly;

interface Report_Interface
{
  /**
   *
   * getResults - Runs the report query against the data source and returns the results
   * @param integer $max_return_rows - Maximum number of rows to return in the result set
   *
   */
  public function getResults($max_return_rows = null);


  /**
   *
   * getColumns - Returns the column names for a given report
   *
   */
  public function getColumns();


  /**
   *
   * getFormConfig - Returns the form configuration for a given report
   *
   */
  public function getFormConfig();


  /**
   *
   * getData - Retrieves the initial data from the data source
   *
   */
  public function getData();

}