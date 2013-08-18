<?php
/**
  *
  * Squerly - SQL-based report class
  * 
  * Report_Sql is used to load data from a SQL database and use it within the rest of the reporting framework
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
class Report_Sql extends Report_Base {

  public $bind_params = array();
  public $processed_query = '';
  public $report_is_running = false;
  //TODO: figure out a better way of doing this
  public static $field_html_classes = array(
    'query' => 'codemirror_mysql',
    'postprocess_code' => 'codemirror_php',
  );

  const REPORT_DISALLOWED_KEYWORD = 'Disallowed keyword found in report; aborting.';
  const REPORT_NOT_SELECT_STATEMENT = 'Report query must be a SELECT statement; aborting.';

  /**
   *
   * _addReportIdentifierComment - Adds a comment to the query for easy identification in logs
   *
   * @todo Add Squerly 'instance', username, and URL of report to the identifying comment
   *
   */
  protected function _addReportIdentifierComment() {
    $report_id_name = '[' . $this->id . '] ' . addslashes($this->name);
    $report_start_time = date("F j, Y @ g:i a T");
    $report_identifier_comment =
    "\n
    /* -==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-

          Powered by Squerly(tm) - http://www.squerly.net

          Report ID/Name: {$report_id_name}
          Report Start Time: {$report_start_time}
          Report Unique ID: {$this->unique_id}
          Host: {$_SERVER['SERVER_NAME']}

       -==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==- */";
    $this->processed_query = $this->processed_query . $report_identifier_comment;
  }


  /**
   *
   * _isValid - checks report SQL for disallowed/descructive keywords
   * @note - This is not completely comprehensive but should act as a basic sanity check for disallowed statements running
   *
   */
  protected function _isValid() {
    $query = SQL::stripComments(strtoupper($this->processed_query)); //Strip comments, and upper-case the SQL

    //Make sure report query starts with 'SELECT '
    if(trim($query) !== '' && substr($query, 0, 6) !== 'SELECT') { F3::error('', self::REPORT_NOT_SELECT_STATEMENT . " Query: {$query}"); }

    $disallowed_keywords = array(
      'ALTER ROUTINE', 'ALTER TABLE', 'ALTER USER', 'COMMIT', 'DELETE ',
      'CREATE ROUTINE', 'CREATE SCHEMA', 'CREATE SEQUENCE', 'CREATE TABLE', 'CREATE USER', 'CREATE VIEW',
      'DROP SCHEMA', 'DROP TABLE', 'DROP USER', 'DROP VIEW', 'EXPLAIN ',
      'GRANT ', 'INSERT ', 'REVOKE ', 'SHUTDOWN', 'LOCK TABLES', 'UPDATE ');

    foreach($disallowed_keywords as $disallowed) {
      if(strpos($query, $disallowed) !== false) { F3::error('', self::REPORT_DISALLOWED_KEYWORD . " Query: {$query}"); }
    }

    return true;
  }


  /**
   *
   * Preprocess the report Query in PHP, strips off comments, removes semi-colons, adds identifier comment to report SQL
   * 
   * @param $max_return_rows integer Maximum number of rows of data to be returned (0 is unlimited)
   * @param $input_values array Array of input key-value pairs to plug into the report query
   *
   */
  protected function _preprocessQuery($max_return_rows = 0, array $input_values = array()) {
    $this->_phpPreprocess(); // Run the query through PHP
    $this->processed_query = SQL::stripComments($this->processed_query); //Strip off all comments
    $this->processed_query = str_replace(';', '', $this->processed_query); //Remove all semi-colons (prevents multiple SQL statements from being run)
    //Use (sanitized) $_GET as bind-parameters unless overridden in $input_values
    $input_values = (empty($input_values)) ? $_REQUEST : $input_values;
    //Swap out the mustache/template tags with bind-parameter placeholders and gets an array of bind parameters/values
    list($this->processed_query, $this->bind_params) = Mustache_Helper::renderSQL($this->processed_query, $input_values);

    //TODO: remove this
    //if($preview) { 
    //  $this->processed_query = SQL::overrideLimit($this->processed_query, self::REPORT_PREVIEW_ROWS); 
    //}
    $this->_addReportIdentifierComment(); //Add identifying comment to the query
  }


  /**
   *
   * Runs the report query against the database and returns the results
   *
   * @param integer $max_return_rows Maximum number of rows of data to be returned (0 is unlimited)
   * @param array $input_values Array of input key-value pairs to plug into the report query
   * @param string $transformation Data transformation to apply to report results
   *
   * @throws Exception if call to $this->getData fails
   *
   * @return Report results
   *
   */
  public function getResults($max_return_rows = 0, array $input_values = array(), $transformation = null) {
    $this->_preprocessQuery($max_return_rows, $input_values); //Pre-process the query through various filters
    if($this->_isValid()) {
      try {
        $this->report_is_running = true;
        $this->getData();
        $this->report_is_running = false;
      } catch(Exception $e) {
        //TODO: Handle exception - display error details in development; generic error message in production
        $this->report_is_running = false;
        throw new Exception($e);
      }
    }
    $this->_postprocessResults($max_return_rows, $transformation);
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
   * getData - Retrieves the initial results data from the data source
   *
   */
  public function getData() {
    $db_adapter = !empty($this->db_adapter) ? 'DB_' . $this->db_adapter : 'DB_Report'; //TODO: move this to constructor
    $this->results = Data_Source::loadSQL($this->processed_query, $this->bind_params, $db_adapter);
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