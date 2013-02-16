<?php
/**
  *
  * Squerly - Report base class
  * 
  * This class is, as the name suggests, the base class that all other report sub-classes should extend
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_Base extends Report_Abstract {

  public $processed_query = '';
  public $results = array();
  public $clean_properties = array();
  public static $model = 'report';


 /**
  *
  * Syncronize the model with the DB
  * 
  * @todo Is this neccessary?
  *
  */
  public function __construct() {
    $this->sync(F3::get('DB_TABLE_PREFIX') . self::$model);
  }


 /**
  *
  * Store a copy of the original properties as loaded from Axon
  *
  */
  public function afterLoad() {
    $this->clean_properties = $this->cast();
  }


  /**
   *
   * Checks report validity
   * 
   * @return boolean - True is valid, False if invalid
   *
   */
  protected function _isValid() {}


  /**
   *
   * Runs the report through PHP buffering to execute any code embedded in the report
   * 
   * @note Requires 'allow_url_include = On' directive to be set in php.ini
   * @see http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-include
   *
   */
  protected function _phpPreprocess() {
    //TODO: refactor this to use allow_url_fopen/temp file ??
    //If $this->query contains '<?' then it's assumed to have embedded PHP code; if so, run it through PHP processing
    $this->processed_query = (strpos($this->query, '<?') !== false) ?
      //This wrapper attempts to do an 'include' first then falls back on on 'eval'
      function($input) {
        //Attempt to turn on required php.ini directives
        ini_set("allow_url_fopen", "1");
        ini_set("allow_url_include", "1");
        ob_start();
        if(ini_get('allow_url_include')) { 
          include "data:text/plain;base64," . base64_encode($input);
        } else {
          eval('?>' . PHP_EOL .  $this->query . PHP_EOL . '<?php ');
        }
        ini_restore("allow_url_include");
        ini_restore("allow_url_fopen");
        return trim(ob_get_clean(), "\n\r\t ");
      } : trim($this->query, "\n\r\t ");
    return true;
  }


  /**
   *
   * Preprocess query through PHP
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  protected function _preprocessQuery($max_return_rows = 0, array $input_values = array()) {}


  /**
   *
   *  Runs the results of the report query through PHP post-processing
   * 
   *  @note $postprocess_function accepts the results of the report as '$results', processes it, and should return the modified results
   *
   */
  protected function _phpPostprocess() {
    $postprocess_function = create_function('$results', $this->postprocess_code);
    $this->results = $postprocess_function($this->results);
  }


  /**
   *
   * _postprocessResults - runs the results of the report query through any necessary post-processing
   *
   */
  protected function _postprocessResults($max_return_rows = 0, $transformation = null) {
    $postprocess_code = String::stripComments($this->postprocess_code);
    if(!empty($postprocess_code)) { $this->_phpPostprocess(); }
    $this->results = Transform::run($this->results, $transformation);
    if($max_return_rows > 0 && sizeof($this->results) > $max_return_rows) { 
      $this->results = array_slice($this->results, 0, $max_return_rows); 
    }
  }


  /**
   *
   * Runs the report query against the data source and returns the results
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  public function getResults($max_return_rows = 0, array $input_values = array(), $transformation = null) {}


  /**
   *
   * Returns the column names for a given report
   *
   */
  public function getColumns() {}


  /**
   *
   * Retrieves the initial results data from the data source
   *
   */
  public function getData() {}


  /**
   *
   * Returns the form configuration for a given report
   *
   */
  public function getFormConfig() {}

}
