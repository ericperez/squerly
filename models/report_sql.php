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
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_Sql extends Report_Base {

  const REPORT_DISALLOWED_KEYWORD = 'Disallowed keyword found in report; aborting.';
  const REPORT_NOT_SELECT_STATEMENT = 'Report query must be a SELECT statment; aborting.';
  const NUM_PREVIEW_ROWS = 10;

  /**
   *
   * _addReportIdentifierComment - Adds a comment to the query for easy identification in logs
   *
   */
  protected function _addReportIdentifierComment() {
    $report_name = addslashes($this->name);
    $report_run_time = strtotime("now");
    $report_unique_id = md5($report_name . $report_run_time);
    $report_identifier_comment =
    "

    /* -==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-

      Report Name: {$report_name}
      Report Run Time: {$report_run_time}
      Report Unique ID: {$report_unique_id}

    -==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==-==- */

    ";
    $this->processed_query = $this->processed_query . $report_identifier_comment;
  }


  /**
   *
   * _isValid - checks report SQL for disallowed/descructive keywords
   * @note - This is not completely comprehensive but should act as a basic sanity check for disallowed statments running
   *
   */
  protected function _isValid() {
    $query = SQL::stripComments(strtoupper($this->processed_query)); //Strip comments, and upper-case the SQL

    //Make sure report query starts with 'SELECT '
    if(substr($query, 0, 7) !== 'SELECT ') { F3::error('', self::REPORT_NOT_SELECT_STATEMENT . " Query: {$query}"); }

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
   * @param $preview boolean - If TRUE, limits the number of rows in the report results to self::REPORT_PREVIEW_ROWS
   *
   */
  protected function _preprocessQuery($preview) {
    $this->_phpPreprocess(); // Run the query through PHP
    $this->processed_query = SQL::stripComments($this->processed_query); //Strip off all comments
    $this->processed_query = str_replace(';', '', $this->processed_query); //Remove all semi-colons (prevents multiple SQL statements from being run)
    //Swap out the mustache/template tags with bound-parameter placeholders and gets an array of bound parameters/values
    list($this->processed_query, $this->bound_params) = Mustache_Helper::renderSQL($this->processed_query, F3::get('GET'));
    if($preview) { 
      $this->processed_query = SQL::overrideLimit($this->processed_query, self::NUM_PREVIEW_ROWS); 
    }
    $this->_addReportIdentifierComment(); //Add identifying comment to the query
  }


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
    if($this->_isValid())
    {
      try {
        $this->getData();
      }
      catch(Exception $e) {
        //TODO: Handle exception - display error details in development; generic error message in production
        throw new Exception($e);
      }
    }
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
   * getData - Retrieves the initial results data from the data source
   *
   */
  public function getData() {
    $this->results = Data_Source::loadSQL($this->processed_query, $this->bound_params, 'DB_Report');
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