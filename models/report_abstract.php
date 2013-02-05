<?php
/**
  *
  * Squerly - Report Abstract
  * 
  * This class defines all the methods that report sub-classes should have.
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
abstract class Report_Abstract extends CRUD implements Report_Interface {

  /**
   *
   * Checks report validity
   * @return boolean - True is valid, False if invalid
   *
   */
  abstract protected function _isValid();


  /**
   *
   * Runs the report through PHP buffering to execute any code embedded in the report
   *
   */
  abstract protected function _phpPreprocess();


  /**
   *
   * Runs the results of the report query through PHP post-processing
   * @note $postprocess_function accepts the results of the report as '$results', processes it, and should return the modified results
   *
   */
  abstract protected function _phpPostprocess();


  /**
   *
   * Preprocess query through PHP
   *    
   * @param $max_return_rows integer Maximum number of rows of data to be returned
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  abstract protected function _preprocessQuery($max_return_rows, array $input_values);


  /**
   *
   * Runs the results of the report query through any necessary post-processing
   *
   */
  abstract protected function _postprocessResults($max_return_rows);

}
