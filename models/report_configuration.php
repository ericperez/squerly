<?php
/**
  *
  * Squerly - Report Saved Configurations Model
  * 
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_Configuration extends CRUD {

 /**
  *
  * This method returns all (enabled) report configuration email schedules (in Crontab format)
  *
  * @return array Array of report configuration IDs and Cron schedules
  *
  */
  public static function getEmailCronSchedules() {
    //TODO: add emails_enabled boolean field to report_configuration ??
    $sql = "
      SELECT rc.id AS report_configuration_id, email_schedule
      FROM report_configuration rc
      INNER JOIN email_schedule es ON(rc.email_schedule_id = es.id)
      WHERE rc.email_schedule_id > 0 AND rc.enabled = 1
    ";
    return DB::sql($sql, null, 59);
  }


 /**
  *
  * Runs the report against the data source using the report configuration input_values as inputs
  * 
  * @param TODO!
  *
  * @return array Report Configuration Results
  *
  */
  public function getResults($max_return_rows = 0, array $input_values = array(), $transformation = null, $output_context = null) {
    $config_properties = array();
    parse_str($this->input_values, $config_properties);
    //TODO: clean this up 
    $max_return_rows = $max_return_rows ?: isset($config_properties['sqrl']['max_return_rows']) ? 
      $config_properties['sqrl']['max_return_rows'] : $max_return_rows; //TODO
    $transformation = $transformation ?: isset($config_properties['sqrl']['transform']) ? 
      $config_properties['sqrl']['transform'] : $transformation;
    $output_context = $output_context ?: isset($config_properties['sqrl']['context']) ? 
      $config_properties['sqrl']['context'] : $output_context;
    $report = Report::load_model($this->report_id);
    $input_values = array();
    parse_str($this->input_values, $input_values);
    return $report->getResults($max_return_rows, $input_values, $transformation);
  }


 /**
  *
  * Enumerates all the report configurations and determines which ones need to be run now
  *
  * @return array Report Configuration IDs
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

}
