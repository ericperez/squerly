<?php
/**
  *
  * Squerly - pCharts export class
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class Export_Pchart implements Export_Interface {

/**
  *
  * Renders 2D array into a Pchart graph
  * 
  * 
  * @param array $data 2D associative array of data to be exported
  * @param string $filename File name of image that is generated
  * @param array $config Array of configuration settings (currently unused)
  * @return binary PNG Image rendering of data
  *
  */
  public static function render(array $data, $filename = NULL, $config = array()) {
    /* pChart library inclusions */
    $base_path = __DIR__ . "/../../vendor/pChart/";
    require_once($base_path . "class/pData.class.php");
    require_once($base_path . "class/pDraw.class.php");
    require_once($base_path . "class/pImage.class.php");
    $pChart = new pData();

    $column_num = 0;
    $column_names = array_keys($data[0]);
    $output = array();
    $row = 0;
    foreach($column_names as $col_name) {
      // First column contains the X-Axis labels
      if($column_num === 0) { 
        $categories = Matrix::pick($data, $col_name);
        $column_num++; 
        continue; 
      }
      //Subsequent columns contain 'data series' for the chart
      $human_col_name = String::humanize($col_name);
      $row_data = array_map(function($in) { return (float) preg_replace('/[^0-9,\.\-e]/', '', $in); }, Matrix::pick($data, $col_name));
      $output = array();
      $pChart->addPoints($row_data, $human_col_name);
      $pChart->setSerieWeight($human_col_name, 0.8);
      $row++;
    }
 
    $chart_image = new pImage(1280, 720, $pChart);
    $chart_image->Antialias = false;

    /* Set the default font */
    $chart_image->setFontProperties(array("FontName" => $base_path . "/fonts/pf_arma_five.ttf", "FontSize" => 8));

    /* Define the chart area */
    $chart_image->setGraphArea(60, 40, 1200, 700);

    /* Draw the scale */
    $scaleSettings = array(
      "XMargin" => 10,
      "YMargin" => 10,
      "Floating" => true,
      "GridR" => 200,
      "GridG" => 200,
      "GridB" => 200,
      "DrawSubTicks" => true,
      "CycleBackground" => true,
    );
    $chart_image->drawScale($scaleSettings);

    /* Turn on Antialiasing */
    $chart_image->Antialias = true;

    /* Draw the line chart */
    $chart_image->drawLineChart();

    /* Write the chart legend */
    $chart_image->drawLegend(20, 10, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

    /* Render the picture (choose the best way) */
    $filename = $filename ?: tempnam('/tmp', 'squerly_results_');
    return $chart_image->autoOutput($filename); 

  }

//TODO: add more customizability
//$pChart->setSerieTicks("Probe 2",4);
//$pChart->setAxisName(0,"Temperatures");
//$pChart->addPoints(array("Jan","Feb","Mar","Apr","May","Jun"),"Labels");
//$pChart->setSerieDescription("Labels","Months");
//$pChart->setAbscissa("Labels");
/* Add a border to the picture */
// $chart_image->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
// /* Write the chart title */ 
// $chart_image->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>11));
// $chart_image->drawText(150,35,"Average temperature",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

}
