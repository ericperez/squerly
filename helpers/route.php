<?php
/**
  *
  * Squerly - Application Request Route Helpers
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Route
{

 /**
  *
  * Returns an array of available application request routes
  *
  * @return array 2D array of available routes in format $controller_name => $routes
  *
  */
 public static function getRoutes() {
    return array(
      //Report Routes
      array('report' =>
        array('GET ' . F3::get('URL_BASE_PATH') . 'report', 'Report_Controller::index', 30),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/index', 'Report_Controller::index', 30),
        //array('GET ' . F3::get('URL_BASE_PATH') . 'report/email/@id', 'Report_Controller::email', 0),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/form/@id', 'Report_Controller::form', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/load/@id', 'Report_Controller::load', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/optionlist', 'Report_Controller::optionlist', 30),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/render/@id', 'Report_Controller::render', 10),
        array('POST ' . F3::get('URL_BASE_PATH') . 'report/render/@id', 'Report_Controller::render', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results', 10),
        array('POST ' . F3::get('URL_BASE_PATH') . 'report/results/@id', 'Report_Controller::results', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'report/validate/@id', 'Report_Controller::validate', 0),
      ),
  
      //Saved Report Routes
      array('saved_report' => 
        array('GET ' . F3::get('URL_BASE_PATH') . 'saved_report', 'Saved_Report_Controller::index', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'saved_report/index', 'Saved_Report_Controller::index', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'saved_report/load/@id', 'Saved_Report_Controller::load', 10),
        array('POST ' . F3::get('URL_BASE_PATH') . 'saved_report/load/@id', 'Saved_Report_Controller::load', 10),
        array('GET ' . F3::get('URL_BASE_PATH') . 'saved_report/getvalues/@id', 'Saved_Report_Controller::getValues', 10),
      ),

    );
  }


 /**
  *
  * Loads all the request routes for the application
  *
  */
  public static function loadAll() {
    $controller_routes = self::getRoutes();
    foreach($controller_routes as $controller_route) {
      foreach($controller_route as $route) {
        F3::route($route[0], $route[1], $route[2]);
      }
    }
    return true;
  }

}
