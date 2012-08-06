<?php
//TODO namespace Squerly;

abstract class Report_Abstract extends CRUD implements Report_Interface
{
  /**
   *
   * _isValid - checks report validity
   * @return boolean - True is valid, False if invalid
   *
   */
  abstract protected function _isValid();


  /**
   *
   * _phpPreprocess - runs the report through PHP buffering to execute any code embedded in the report
   *
   */
  abstract protected function _phpPreprocess();


  /**
   *
   * _phpPostprocess - runs the results of the report query through PHP post-processing
   * @note $postprocess_function accepts the results of the report as '$results', processes it, and should return the modified results
   *
   */
  abstract protected function _phpPostprocess();


  /**
   *
   * _preprocessQuery - preprocess in PHP, strips off comments, removes semi-colons, adds identifier comment to report SQL
   * @param integer $max_return_rows - Maximum number of rows to return in the result set
   *
   */
  abstract protected function _preprocessQuery($max_return_rows);


  /**
   *
   * _postprocessQuery - runs the results of the report query through any necessary post-processing
   *
   */
  abstract protected function _postprocessResults();

}
