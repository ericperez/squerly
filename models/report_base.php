<?php
//TODO namespace Squerly;

class Report_Base extends Report_Abstract
{

  public $processed_query = '';
  public $bound_params = array(); //TODO: move this
  public $results = array();  


  //Syncronize the model with the DB
  public function __construct() {
    $this->sync(F3::get('DB_TABLE_PREFIX') . 'report');
  }


  /**
   *
   * _isValid - checks report validity
   * @return boolean - True is valid, False if invalid
   *
   */
  protected function _isValid() {}


  /**
   *
   * _phpPreprocess - runs the report through PHP buffering to execute any code embedded in the report
   * @todo - find a better solution than 'eval' for this: http://www.php.net/manual/en/wrappers.data.php#106021 ??
   *
   */
  protected function _phpPreprocess() {
    ob_start();
    eval('?>' . $this->query . '<?php ');
    $this->processed_query = ob_get_contents();
    ob_end_clean();
  }


  /**
   *
   * _preprocessQuery - preprocess in PHP, strips off comments, removes semi-colons, adds identifier comment to report SQL
   * @param integer $max_return_rows - Maximum number of rows to return in the result set
   *
   */
  protected function _preprocessQuery($max_return_rows) {}


  /**
   *
   * _phpPostprocess - runs the results of the report query through PHP post-processing
   * @note $postprocess_function accepts the results of the report as '$results', processes it, and should return the modified results
   *
   */
  protected function _phpPostprocess() {
    $postprocess_function = create_function('$results', $this->postprocess_code);
    $this->results = $postprocess_function($this->results);
  }


  /**
   *
   * _postprocessQuery - runs the results of the report query through any necessary post-processing
   *
   */
  protected function _postprocessResults() {}


  /**
   *
   * getResults - Runs the report query against the data source and returns the results
   * @param integer $max_return_rows - Maximum number of rows to return in the result set
   *
   */
  public function getResults($max_return_rows = null) {}


  /**
   *
   * getColumns - Returns the column names for a given report
   *
   */
  public function getColumns() {}


  /**
   *
   * getData - Retrieves the initial results data from the data source
   *
   */
  public function getData() {}


  /**
   *
   * getFormConfig - Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {}

}
