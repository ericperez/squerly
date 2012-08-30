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
  * @todo Add a record 'copy' route
  * @todo Consolidate shared code that exists across routes/actions
  * 
  */
class Crud_Controller Implements Crud_Controller_Interface {

  //This is used by child classes to assign custom forms to CRUD actions/routes
  //CRUD controller itself uses default forms (empty array)
  protected static $_forms = array();

 /**
  *
  * Set up all the CRUD routes
  *   
  *
  */
  public static function setUpRoutes() {
    //TODO: add permissions handling
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/optionlist', 'Crud_Controller::optionlist', 30);

    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model', 'Crud_Controller::index', 10);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/add', 'Crud_Controller::add', 600);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/delete/@id', 'Crud_Controller::delete', 600);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/edit/@id', 'Crud_Controller::edit', 600);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/export', 'Crud_Controller::exportMultiple', 10);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/export/@id', 'Crud_Controller::exportOne', 10);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/search', 'Crud_Controller::search', 60);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/searchresults', 'Crud_Controller::searchResults', 10);
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '@model/view/@id', 'Crud_Controller::view', 10);
    F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/add/token/@token', 'Crud_Controller::addEditProcess');
    F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/edit/@id/token/@token', 'Crud_Controller::addEditProcess');
    F3::route('POST ' . F3::get('URL_BASE_PATH') . '@model/delete/@id/token/@token', 'Crud_Controller::deleteProcess');
  }


 /**
  *
  * Allows CRUD-extending classes to have their code called via CRUD controller 
  * 
  * @param string $model Name of model
  * @param string $action Name of controller action/method to call
  *   
  *
  */
  public static function delegate($model, $action) {
    $controller_class = String::machine($model, true) . '_Controller';
    if(!@class_exists($controller_class)) { return false; }
    $class_implements = @class_implements($controller_class) ?: array();
    if(!in_array('Crud_Controller_Interface', $class_implements)) { 
      F3::error('', "CRUD controller sub-classes must implement Crud_Controller_Interface"); 
    }
    return $controller_class::$action(false);
  }


 /**
  *
  * 'List Records/Index' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'index' method; if false, standard CRUD code is run
  *   
  */
  public static function index($try_to_delegate = true) {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'index') !== false) { return; }
    //TODO: create an array to hold the F3 vars and set values in a loop
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('index')); }
    $records_name = Inflector::plural($model_friendly);
    if(!F3::exists('title')) { F3::set('title', $records_name); }
    if(!F3::exists('page_title')) { F3::set('page_title', $records_name . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('content')) { 
      $limit = F3::get('RECORDS_PER_PAGE');
      $page = (int) F3::get('GET.page') ?: 1;
      $records = CRUD::loadRecords($limit, $page, false);
      if(!empty($records)) {
        F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'));
      } else {
        F3::set('content', "No {$records_name} Found");
      }
      //TODO: Set vars for pagination controls
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Add Record' action
  *
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'add' method; if false, standard CRUD code is run
  * 
  */
  public static function add($try_to_delegate = true) {
    //if(!F3::get('SESSION.user')) { F3::reroute(F3::get('URL_BASE_PATH') . 'auth/login'; }; //TODO: Permission Check
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if($try_to_delegate && Crud_Controller::delegate($model, 'add') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('add')); }
    $title = 'Add ' . $model_friendly;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    if(!F3::exists('form')) { 
      $form_config = array(
        'action' => F3::get('URL_BASE_PATH') . $model . "/add/token/{$csrf_token}",
        'method' => 'post',
      );
      //var_dump(self::$_forms); exit;
      $form = (isset(self::$_forms['add']) && Crud_Helper::getForm(self::$_forms['add']))
        ?: CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', $form);
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Edit Record' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'add' method; if false, standard CRUD code is run
  *
  */
  public static function edit($try_to_delegate = true) {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'edit') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('edit')); }
    $title = 'Edit ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    if(!F3::exists('form')) { 
      $form_config = array(
        'action' => F3::get('URL_BASE_PATH') . $model . "/edit/${id}/token/{$csrf_token}",
        'method' => 'post',
      );
      $form = CRUD_Helper::buildFormFromModel($model, array(), F3::get('record'), $form_config);
      F3::set('form', $form);
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Add and Edit Record POST/Processing' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'addEditProcess' method; if false, standard CRUD code is run
  *
  */
  public static function addEditProcess($try_to_delegate = true) {
    $id = (int) F3::get('PARAMS.id') ?: null;
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'addEditProcess') !== false) { return; }

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


 /**
  *
  * 'Delete Record' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'index' method; if false, standard CRUD code is run
  *
  */
  public static function delete($try_to_delegate = true) {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'delete') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('delete')); }
    $record = CRUD::loadRecord($model);
    $title = 'Delete ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('content')) { 
      F3::set('content', "<br/>Are you sure you want to delete {$model_friendly} {$id}?<br/>");
    }
    if(!F3::exists('form')) { 
      $form = CRUD_Helper::buildDeleteForm($model, $id);
      F3::set('form', $form);
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Delete Record POST/Processing' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'index' method; if false, standard CRUD code is run
  *
  */
  public static function deleteProcess($try_to_delegate = true) {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'deleteProcess') !== false) { return; }
    $model_path = F3::get('URL_BASE_PATH') . $model;
    $delete_param = F3::get('POST.delete');
    //TODO: validate the CSRF token
    if($delete_param !== 'Yes') { 
      Notify::info("{$model_friendly} {$id} was not deleted.");
      F3::reroute($model_path);
      return;
    }
    $record = CRUD::loadRecord($model);
    $record[0]->erase();
    Notify::info("{$model_friendly} {$id} deleted successfully.");
    F3::reroute($model_path);
  }


 /**
  *
  * 'Export Single Record' action
  *
  */
  public static function exportOne() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    echo Export::render(array(F3::get('record')), $model);
  }


 /**
  *
  * 'Export Multiple Records' action
  * 
  * @todo: consolidate with exportOne
  *
  */
  public static function exportMultiple() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    F3::set('page_title', $model_friendly . ' Home ' . F3::get('PAGE_TITLE_BASE'));
    $limit = F3::get('RECORDS_PER_PAGE'); //TODO: Update limit/page to only apply on 'table' view??
    $page = (int) F3::get('GET.page') ?: 1;
    //TODO: fix issue with {{id}} being parsed by F3 templating system
    echo Export::render(CRUD::loadRecords($limit, $page, false), $model);
  }


 /**
  *
  * 'HTML Select' Action - Echos ID/name value pairs for a given model as an HTML select element
  * 
  * This can be used in AJAX calls to populate the innerHTML of a DIV with the list of available reports
  * 
  * @todo: Allow config to be passed in or read from GET params
  *
  */
  public static function optionlist($config = null) {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $options = array('' => '(No Selection)') + CRUD::pairs($model, true);
    $config = $config ?: array(
      'id' => 'report_id',
      'name' => 'report_id'
    );
    $output = Form::select($model, $options, NULL, $config) . PHP_EOL;
    echo $output;
  }


 /**
  *
  * 'Search Records' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'index' method; if false, standard CRUD code is run
  *
  */
  public static function search($try_to_delegate = true) {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'search') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('search')); }
    $title = 'Search ' . $model_friendly . ' Records';
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('form')) {
      $form_config = array(
        'action' => F3::get('URL_BASE_PATH') . $model . "/searchresults",
        'method' => 'get',
      );
      $form = CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', $form);
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Search Results' action
  * 
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'index' method; if false, standard CRUD code is run
  *
  */
  public static function searchResults($try_to_delegate = true) {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'searchResults') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('search')); }
    $title = $model_friendly . ' Search Results';
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('form')) { 
      $form_config = array(
        'action' => F3::get('URL_BASE_PATH') . $model . "/searchresults",
        'method' => 'get',
        'header' => 'Perform Another Search',
        'collapsed' => true
      );
      $form = CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', $form);
    }
    $limit = F3::get('RECORDS_PER_PAGE');
    $page = (int) F3::get('GET.page') ?: 1;
    $records = CRUD::loadRecords($limit, $page, false);
    if(!F3::exists('content')) { 
      if(!empty($records)) {
        F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'));
      } else {
        $records_name = Inflector::plural($model_friendly);
        F3::set('content', "No {$records_name} match the specified criteria.");
      }
    }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'View Record' action
  *
  * @param boolean $try_to_delegate If true, an attempt will be made to find a Crud_Controller-extending
  *   class (based on the model name) and run code in it's 'view' method; if false, standard CRUD code is run
  * 
  */
  public static function view($try_to_delegate = true) {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If controller exists for the specific CRUD model, call that first
    if($try_to_delegate && Crud_Controller::delegate($model, 'view') !== false) { return; }
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('view')); }
    $title = 'View ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    //TODO: fix issue with {{id}} being parse by F3 templater
    $record_content = array(F3::get('record'));
    if(!F3::exists('content')) { F3::set('content', Export::render($record_content, 'table'), $model); }
    if(!F3::exists('flash_msgs')) { F3::set('flash_msgs', Notify::renderAll()); }
    echo Template::serve('layout.html');
  }

}
