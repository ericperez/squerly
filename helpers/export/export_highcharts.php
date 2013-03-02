<?php
/**
  *
  * Squerly - Highcharts JSON export class
  * 
  * Note: Highcharts is NOT free to use for commercial sites; see license below
  * @link http://shop.highsoft.com/highcharts.html
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Highcharts implements Export_Interface {

/**
  *
  * Renders 2D array as a Highcharts JSON configuration string
  * 
  * Utilizes Highroller PHP library to render 2D array data as a Highcharts configuration JSON string
  * @link http://highroller.io
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename Currently used to generate the chart title (@todo use $config)
  * @param array $config Array of configuration settings (currently unused)
  * @return string Highcharts JSON configuration string
  *
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    $chart = new HighRoller();

    //TODO: all of these settings should be customizable through $config
    $chart->chart->type = 'line';
    $chart->chart->renderTo = 'report_results';
    $chart->chart->zoomType = 'x';
    //$chart->title->text = String::humanize($filename); //TODO: improve this

    $chart->xAxis = new HighRollerXAxis();
    $chart->xAxis->type = 'column'; //linear
    $chart->xAxis->labels->rotation = -45;
    $chart->xAxis->labels->align = 'right';

    $chart->credits = new HighRollerCredits();
    $chart->credits->enabled = false;

    $chart->legend = new HighRollerLegend();
    $chart->legend->style = json_decode('
      "style": {
        "left": "auto",
        "bottom": "auto",
        "right": "10px",
        "top": "100px"
      }
    ');

    //Cycle through each column of data and convert it into a series on the chart
    $column_num = 0;
    $column_names = array_keys($data[0]);
    foreach($column_names as $col_name) {
      // First column contains the X-Axis labels
      if($column_num === 0) { 
        $chart->xAxis->categories = Matrix::pick($data, $col_name);
        $column_num++; 
        continue; 
      }
      //Subsequent columns contain 'data series' for the chart
      $series_data = array_map(function($in) { return (float) preg_replace('/[^0-9,\.\-e]/', '', $in); }, Matrix::pick($data, $col_name));
      $series = new HighRollerSeriesData();
      $series
        ->addName(String::humanize($col_name))
        ->addData($series_data);
      $chart->addSeries($series);
    }
    return json_encode($chart);
  }

}
