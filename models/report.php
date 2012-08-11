<?php
/**
  *
  * Squerly - Report main class
  * 
  * Report is the main 'report' class in Squerly that all report sub-classes are instantiated from through the 'deletgate' factory method
  * 
  * If you are interfacing with reports in Squerly, this class is the one you should be using. All 'reports' share the same data structure
  * but each 'type' of report has it's on PHP class based on it's default data source i.e. SQL database, XML file, CSV file, etc.
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report extends Report_Base {

  public $sub_class;

  //TODO: clean this up
  public function afterLoad() {
    $type = isset($this->type) && !empty($this->type) ? $this->type : 'sql'; //Defaults to SQL-based report
    $sub_class = String::machine('report_' . $type, true);
    //$sub_class_file = String::machine($sub_class);
    if(@class_exists($sub_class) && is_subclass_of($sub_class, 'Report_Base')) {
      $this->sub_class = $sub_class;
    } else {
      F3::error('', "Report class {$sub_class} not found.");
    }
  }


  //Report factory
  public static function delegate($id) {
    $report = new self();
    $report->load("id = {$id}");
    $report_sub_class = new $report->sub_class();
    return $report_sub_class->load("id = {$id}");
  }

}
