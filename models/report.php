<?php
//TODO namespace Squerly;

class Report extends Report_Base
{
  public $sub_class;

  //TODO: clean this up
  public function afterLoad() {
    $type = isset($this->type) && !empty($this->type) ? $this->type : 'sql'; //Defaults to SQL-based report
    $sub_class = String::modelToClass('report_' . $type);
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
