<?php
/**
  *
  * Squerly - CRUD Controller
  * 
  * Squerly is built on top of the 'Fat-Free Framework (F3)' (@link http://bcosca.github.com/fatfree/)
  * F3 contains an amazing CRUD/ORM system named 'Axon' which automatically derives the model structure 
  * and properties based on the database table the model is sync'd to. This controller defines very generic 
  * routes that complement the 'CRUD' interface that Axon provides so you can add/edit/delete/view/export/search
  * records from any database table by simply adding them to the 'CRUD_TABLE_WHITELIST' array defined in the
  * squerly.config.php file under '[Squerly_Root]/config/'
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */


class Crud_Controller {
  //TODO: consolidate shared code that exists across actions

  //'Records List/Index' action
  public static function index() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('index'));
    $records_name = Inflector::plural($model_friendly);
    F3::set('title', $records_name);
    F3::set('page_title', $records_name . F3::get('PAGE_TITLE_BASE'));
    $limit = F3::get('RECORDS_PER_PAGE');
    $page = (int) F3::get('GET.page') ?: 1;
    $records = CRUD::loadRecords($limit, $page, false);
    if(!empty($records)) {
      F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'));
    } else {
      F3::set('content', "No {$records_name} Found");
    }
    //TODO: Set vars for pagination controls
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Add Record' action
  public static function add() {
    //if(!F3::get('SESSION.user')) { F3::reroute(F3::get('URL_BASE_PATH') . 'auth/login'; }; //TODO: Permission Check
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('add'));
    $title = 'Add ' . $model_friendly;
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    $form_config = array(
      'action' => F3::get('URL_BASE_PATH') . $model . "/add/token/{$csrf_token}",
      'method' => 'post',
    );
    F3::set('form', CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config));
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Edit Record' action
  public static function edit() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('edit'));
    $title = 'Edit ' . $model_friendly . ' ' . $id;
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    $form_config = array(
      'action' => F3::get('URL_BASE_PATH') . $model . "/edit/${id}/token/{$csrf_token}",
      'method' => 'post',
    );
    F3::set('form', CRUD_Helper::buildFormFromModel($model, array(), F3::get('record'), $form_config));
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Add and Edit POST' action
  public static function addEditProcess() {
    $id = (int) F3::get('PARAMS.id') ?: null;
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $record = new Axon($model);
    
    //TODO: add form validation
    //TODO: validate csrf token (kohana version)

    $primary_key = Db_Meta::getPrimaryKeys($model);
    if($id > 0) { $record->load("{$primary_key}={$id}"); };
    //$allowed_fields = array_diff_key();
    $record->copyFrom('POST'); //, $allowed_fields); //TODO: create blacklist of fields to not accept from POST
    $saved_id = $record->save();
    $action = ($id > 0 && $saved_id === null) ? "{$id} updated" : 'added';
    Notify::info("{$model_friendly} {$action} successfully.");
    F3::reroute(F3::get('URL_BASE_PATH') . $model);
  }


  //'Delete Record' action
  public static function delete() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('delete'));
    $record = CRUD::loadRecord($model);
    $title = 'Delete ' . $model_friendly . ' ' . $id;
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    F3::set('content', "<br/>Are you sure you want to delete {$model_friendly} {$id}?<br/>");
    F3::set('form', CRUD_Helper::buildDeleteForm($model, $id));
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Delete Record POST' action
  public static function deleteProcess() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $model_path = F3::get('URL_BASE_PATH') . $model;
    $delete_param = F3::get('POST.delete');
    //TODO: validate the CSRF token
    if($delete_param !== 'Yes') { 
      Notify::info("{$model_friendly} {$id} was not deleted.");
      F3::reroute($model_path); 
    }
    $record = CRUD::loadRecord($model);
    $record[0]->erase();
    Notify::info("{$model_friendly} {$id} deleted successfully.");
    F3::reroute($model_path);
  }


  //'Single Record Export' action
  public static function exportOne() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    echo Export::render(array(F3::get('record')), $model);
  }


  //'Multiple Records export' action
  //TODO: consolidate with exportOne
  public static function exportMultiple() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('page_title', $model_friendly . ' Home ' . F3::get('PAGE_TITLE_BASE'));
    $limit = F3::get('RECORDS_PER_PAGE'); //TODO: Update limit/page to only apply on 'table' view??
    $page = (int) F3::get('GET.page') ?: 1;
    //TODO: fix issue with {{id}} being parsed by F3 templating system
    echo Export::render(CRUD::loadRecords($limit, $page, false), $model);
  }


  //'View Record' action
  public static function view() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('view'));
    $title = 'View ' . $model_friendly . ' ' . $id;
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    //TODO: fix issue with {{id}} being parse by F3 templater
    $record_content = array(F3::get('record'));
    F3::set('content', Export::render($record_content, 'table'), $model);
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Record Search' action
  public static function search() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('search'));
    $title = 'Search ' . $model_friendly . ' Records';
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    $form_config = array(
      'action' => F3::get('URL_BASE_PATH') . $model . "/searchresults",
      'method' => 'get',
    );
    F3::set('form', CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config));
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }


  //'Search Results' action
  public static function searchResults() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('navigation', CRUD_Helper::navigation('search'));
    $title = $model_friendly . ' Search Results';
    F3::set('title', $title);
    F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE'));
    $form_config = array(
      'action' => F3::get('URL_BASE_PATH') . $model . "/searchresults",
      'method' => 'get',
      'header' => 'Perform Another Search',
      'collapsed' => true
    );
    F3::set('form', CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config));
    $limit = F3::get('RECORDS_PER_PAGE');
    $page = (int) F3::get('GET.page') ?: 1;
    $records = CRUD::loadRecords($limit, $page, false);
    if(!empty($records)) {
      F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'));
    } else {
      $records_name = Inflector::plural($model_friendly);
      F3::set('content', "No {$records_name} match the specified criteria.");
    }
    F3::set('flash_msgs', Notify::renderAll());
    echo Template::serve('layout.html');
  }

}


//Define all the CRUD routes
//TODO: add permissions handling
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model', 'Crud_Controller::index');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/add', 'Crud_Controller::add');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/delete/@id', 'Crud_Controller::delete');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/edit/@id', 'Crud_Controller::edit');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/export', 'Crud_Controller::exportMultiple');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/export/@id', 'Crud_Controller::exportOne');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/search', 'Crud_Controller::search');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/searchresults', 'Crud_Controller::searchResults');
F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/view/@id', 'Crud_Controller::view');
F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/add/token/@token', 'Crud_Controller::addEditProcess');
F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/edit/@id/token/@token', 'Crud_Controller::addEditProcess');
F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/delete/@id/token/@token', 'Crud_Controller::deleteProcess');
