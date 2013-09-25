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
 * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
 * @license GNU General Public License, version 3 or later
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.squerly.net
 *
 */
class Report_Php extends Report_Base {

  /**
   *
   * Gets the result set back from the data source (in this case, PHP code)
   *
   * @param integer $max_return_rows Maximum number of rows of data to be returned (0 is unlimited)
   * @param array $input_values Array of input key-value pairs to plug into the report query
   * @param string $transformation Data transformation to apply to report results
   *
   * @return Report results
   *
   */
  public function getResults($max_return_rows = 0, array $input_values = array(), $transformation = null) {
    $this->_preprocessQuery($max_return_rows, $input_values); //Pre-process the query through various filters
    $this->_postprocessResults($max_return_rows, $transformation);
    return $this->results;
  }


  /**
   *
   * Returns the column names for a given report
   *
   */
  public function getColumns() {
    //@todo: Implement
  }


  /**
   *
   * Stub
   *
   * @note A 2D Array should be returned by PHP code that is implemented in the report->postprocess_code property
   *
   */
  public function getData() { }


  /**
   *
   * Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {
    //@todo: Implement
  }

}
