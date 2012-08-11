<?php

class Export_Highcharts implements Export_Interface {

  //Utilizes Highroller PHP library to render 2D array data as a Highcharts configuration JSON string
  public static function render(array $data, $filename = NULL, $config = array()) {
    require_once(__DIR__ . '/../vendor/HighRoller.php');
    require_once(__DIR__ . '/../vendor/HighRollerSeriesData.php');
    if(!isset($data[0])) { return '{}'; } //Empty data set, nothing to graph

    $chart = new HighRoller();

    //TODO: all of these settings should be customizable through $config
    $chart->chart->type = 'line';
    $chart->chart->renderTo = 'squerly_results_div';
    $chart->title->text = String::humanize($filename); //TODO: improve this

    $chart->xAxis = new HighRollerXAxis();
    $chart->xAxis->type = 'linear';
    $chart->xAxis->labels->rotation = -45;
    $chart->xAxis->labels->align = 'right';

    $chart->credits = new HighRollerCredits();
    $chart->credits->enabled = false;

    //Cycle through each column of data and convert it into a series on the chart
    $column_num = 0;
    $columns = array_keys($data[0]);
    foreach($columns as $col_name) {
      // First column contains the X-Axis labels
      if($column_num === 0) { 
        $chart->xAxis->categories = Matrix::pick(&$data, $col_name);
        $column_num++; 
        continue; 
      }
      //Subsequent columns contain 'data series' for the chart
      $series_data = array_map('intval', Matrix::pick(&$data, $col_name));
      $series = new HighRollerSeriesData();
      $series
        ->addName(String::humanize($col_name))
        ->addData($series_data);
      $chart->addSeries($series);
    }
    return json_encode($chart);
  }

}
