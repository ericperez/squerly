<?php

class Export_Hichcharts implements Export_Interface {

  //TODO: finish this
  //Utilizes Highroller PHP library to render 2D array data as a Highcharts configuration JSON string
  public static function render(array $data, $filename = NULL, $config = array()) {
    require_once(__DIR__ . '/../vendor/HighRoller.php');
    require_once(__DIR__ . '/../vendor/HighRollerSeriesData.php');
      
    $chartData = array(5324, 7534, 6234, 7234, 8251, 10324);
    $series1 = new HighRollerSeriesData();
    $series1->addName('myData')->addData($chartData);

    $linechart = new HighRoller();
    $linechart->chart->type = 'line';
    $linechart->chart->renderTo = 'render_div';
    $linechart->title->text = 'Hello HighRoller';
    //$linechart->yAxis->title->text = 'Total';
    $linechart->addSeries($series1);
    
    return $linechart->renderChart();
  }

}