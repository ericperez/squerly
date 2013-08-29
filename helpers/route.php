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
    $base_path = F3::get('URL_BASE_PATH') . '@instance/';

    return array(
      //CRUD Routes
      array('crud' =>
        array('GET '  . $base_path . '@model/optionlist', 'Crud_Controller::optionlist', 10),
        array('GET '  . $base_path . '@model', 'Crud_Controller::index', 0),
        array('GET '  . $base_path . '@model/index', 'Crud_Controller::index', 0),
        array('GET '  . $base_path . '@model/add', 'Crud_Controller::add', 10),
        array('GET '  . $base_path . '@model/delete/@id', 'Crud_Controller::delete', 10),
        array('GET '  . $base_path . '@model/edit/@id', 'Crud_Controller::edit', 0),
        array('GET '  . $base_path . '@model/copy/@id', 'Crud_Controller::copy', 0),
        array('GET '  . $base_path . '@model/export', 'Crud_Controller::exportMultiple', 10),
        array('GET '  . $base_path . '@model/export/@id', 'Crud_Controller::exportOne', 10),
        array('GET '  . $base_path . '@model/migrate', 'Crud_Controller::migrate', 10),
        array('GET '  . $base_path . '@model/search', 'Crud_Controller::search', 10),
        array('GET '  . $base_path . '@model/searchresults', 'Crud_Controller::searchResults', 10),
        array('GET '  . $base_path . '@model/view/@id', 'Crud_Controller::view', 10),
        array('POST ' . $base_path . '@model/add/token/@token', 'Crud_Controller::addEditProcess', 0),
        array('POST ' . $base_path . '@model/edit/@id/token/@token', 'Crud_Controller::addEditProcess', 0),
        array('POST ' . $base_path . '@model/delete/@id/token/@token', 'Crud_Controller::deleteProcess', 0),
        array('POST ' . $base_path . '@model/add/token/@token/redirect/@redirect', 'Crud_Controller::addEditProcess', 0),
        array('POST ' . $base_path . '@model/edit/@id/token/@token/redirect/@redirect', 'Crud_Controller::addEditProcess', 0),
        array('POST ' . $base_path . '@model/delete/@id/token/@token/redirect/@redirect', 'Crud_Controller::deleteProcess', 0),
      ),

      //Report Routes
      array('report' =>
        array('GET ' . $base_path . 'report', 'Report_Controller::index', 0),
        array('GET ' . $base_path . 'report/index', 'Report_Controller::index', 0),
        //array('GET ' . $base_path . 'report/email/@id', 'Report_Controller::email', 0),
        array('GET ' . $base_path . 'report/form/@id', 'Report_Controller::form', 10),
        array('GET ' . $base_path . 'report/load/@id', 'Report_Controller::load', 10),
        array('GET ' . $base_path . 'report/optionlist', 'Report_Controller::optionlist', 30),
        array('GET ' . $base_path . 'report/render/@id', 'Report_Controller::render', 10),
        array('POST ' . $base_path . 'report/render/@id', 'Report_Controller::render', 10),
        array('GET ' . $base_path . 'report/results/@id', 'Report_Controller::results', 10),
        array('POST ' . $base_path . 'report/results/@id', 'Report_Controller::results', 10),
        array('GET ' . $base_path . 'report/validate/@id', 'Report_Controller::validate', 0),
      ),

      //Saved Report Routes
      array('saved_report' =>
        array('GET ' . $base_path . 'saved_report', 'Saved_Report_Controller::index', 10),
        array('GET ' . $base_path . 'saved_report/index', 'Saved_Report_Controller::index', 10),
        array('GET ' . $base_path . 'saved_report/load/@id', 'Saved_Report_Controller::load', 10),
        array('POST ' . $base_path . 'saved_report/load/@id', 'Saved_Report_Controller::load', 10),
        array('GET ' . $base_path . 'saved_report/getvalues/@id', 'Saved_Report_Controller::getValues', 10),
      ),

    );
  }


  /**
   *
   * //Home Page route
   * @todo Route to 'default' instance
   * @todo REMOVE THIS; integrate into getRoutes
   *
   */
  public static function rootRoute() {
    F3::route('GET ' . F3::get('URL_BASE_PATH'),
      function() {
        list($model) = CRUD_Helper::getModelName(true);
        F3::reroute(F3::get('URL_BASE_PATH') . $model);
      }
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
