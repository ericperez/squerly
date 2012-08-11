<?php
/**
  *
  * Squerly - Report Interface
  * 
  * This interface describes the API for all 'report' classes.
  * The core 'Report' class implements this, therefore all report subclasses must implement it as well
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */


//TODO namespace Squerly;
interface Report_Interface
{

  /**
   *
   * Runs the report query against the data source and returns the results
   * @param integer $max_return_rows - Maximum number of rows to return in the result set
   *
   */
  public function getResults($max_return_rows = null);


  /**
   *
   * Returns the column names for a given report
   *
   */
  public function getColumns();


  /**
   *
   * Returns the form configuration for a given report
   *
   */
  public function getFormConfig();


  /**
   *
   * Retrieves the initial data from the data source
   *
   */
  public function getData();

}
