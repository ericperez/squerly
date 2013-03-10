<?php
/**
  *
  * Squerly - Saved Report Form Configurations Model
  * 
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Saved_Report extends CRUD {

 /**
  *
  * Returns an associative array of configuration values for this saved report
  * 
  * @return array Saved Report Configuration configuration values
  *
  */
  public function getConfig() {
    $saved_report_values = $this->getInputValues();
    $config_keys = array('max_return_rows' => 0, 'transform' => null, 'context' => null); //TODO: pull these from a unified place
    return $saved_report_values['sqrl'] + $config_keys;
  }

 /**
  *
  * Returns an associative array of saved form values for a given report
  * 
  * @return array Saved Report Configuration form values
  *
  */
  public function getInputValues() {
    $output = array();
    parse_str($this->input_values, $output);
    return $output;
  }


 /**
  *
  * Runs the report against the data source using the saved report configuration input_values as inputs
  *
  * @return array Saved Report Configuration Results
  *
  */
  public function getResults() {
    $config = $this->getConfig();
    $saved_report_values = $this->getInputValues();
    $report = Report::load_model($this->report_id);
    return $report->getResults($config['max_return_rows'], $saved_report_values, $config['transform']);
  }


 /**
  *
  * Enumerates all the saved report configurations and determines which ones need to be run now
  *
  * @return array Saved Report Configuration IDs
  * @todo refactor this using new 'schedule / events' tables !!!!
  *
  */
  public static function getConfigsScheduledToRun() {
    $email_schedules = self::getEmailCronSchedules();
    $cron_parser = new CronExpression();
    $output = array();
    foreach($config_schedules as $config_schedule) {
      if($cron_parser->isDue($config_schedule['email_schedule'])) {
        $output[] = $config_schedule['id'];
      }
    }
    return $output;
  }


 /**
  *
  * This method returns all (enabled) saved report configuration email schedules (in Crontab format)
  *
  * @return array Array of report configuration IDs and Cron schedules
  * @todo refactor this using new 'schedule / events' tables !!!!
  *
  */
  public static function getEmailCronSchedules() {
    //TODO: update this query when data structure is finalized
    return;
    $sql = "
      SELECT sr.id AS saved_report_id, email_schedule
      FROM saved_report sr
      INNER JOIN email_schedule es ON(sr.email_schedule_id = es.id)
      WHERE sr.email_schedule_id > 0 AND sr.enabled = 1
    ";
    return DB::sql($sql, null, 59);
  }

}
