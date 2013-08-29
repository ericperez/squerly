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
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  * @todo Consolidate shared code that exists across routes/actions
  * 
  */
class Crud_Controller implements Crud_Controller_Interface {

  //This is used by child classes to assign custom forms to CRUD actions/routes
  //CRUD controller itself uses default forms (empty array)
  protected static $_forms = array();

 /**
  *
  * Controller Initialization
  *   
  */
  public static function init() {
    //TODO: add permissions check
    //TODO: use these to build JS/CSS blocks
    F3::set('javascript', CRUD_Helper::getBaseJavascript());
    F3::set('css', CRUD_Helper::getBaseStylesheets());
  }


 /**
  *
  * Sets the 'content' registry value to the fields (in $fields) of the current CRUD model
  * 
  * @param string $fields Comma-delimited list of fields that will show up on the Index actions 
  *   
  */
  protected static function _getIndexRecords($fields) {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $limit = F3::get('RECORDS_PER_PAGE');
    $page = (int) F3::get('GET.page') ?: 1;
    $primary_key = Db_Meta::getPrimaryKeys($model);
    $records = CRUD::loadRecords($fields, $limit, $page, false, null, $primary_key . ' DESC');
    if(!empty($records)) {
      F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'), false, false);
    } else {
      $records_name = Inflector::plural($model_friendly);
      F3::set('content', "No {$records_name} Found");
    }
  }


 /**
  *
  * 'List Records/Index' action
  *   
  */
  public static function index() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //TODO: create an array to hold the F3 vars and set values in a loop
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('index')); }
    $records_name = Inflector::plural($model_friendly);
    if(!F3::exists('title')) { F3::set('title', $records_name); }
    if(!F3::exists('page_title')) { F3::set('page_title', $records_name . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('content')) { 
      self::_getIndexRecords('*');
      //TODO: Set vars for pagination controls
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Add Record' action
  * 
  */
  public static function add() {
    //if(!F3::get('SESSION.user')) { F3::reroute(F3::get('URL_BASE_PATH') . 'auth/login'; }; //TODO: Permission Check
    F3::clear('content');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('add')); }
    $title = 'Add ' . $model_friendly;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    if(!F3::exists('form')) {
      $model_path = CRUD_Helper::getModelPath();
      $form_config = array(
        'action' => $model_path . "/add/token/{$csrf_token}/redirect/true",
        'method' => 'post',
      );
      $form_markup_arr = (isset(self::$_forms['add']) && Crud_Helper::getForm(self::$_forms['add']))
        ?: CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', join(PHP_EOL, $form_markup_arr), false, false);
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }



 /**
  *
  * 'Copy Record' action
  *
  * @todo Clean this up; currently a lot of this code is copy/pasted from the 'add' action
  * 
  */
  public static function copy() {
    F3::clear('content');
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('copy')); }
    $title = 'Copy ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    $record = CRUD::loadRecord($model);
    $record[0]->id = null; //Unset the 'id' field; TODO: this should look up the 'actual' primary key
    $record[0]->created_at = null;
    $record[0]->copyTo('record');
    if(!F3::exists('form')) {
      $model_path = CRUD_Helper::getModelPath();
      $form_config = array(
        'action' => $model_path . "/add/token/{$csrf_token}/redirect/true",
        'method' => 'post',
      );
      $values = F3::get('record');
      $form_markup_arr = CRUD_Helper::buildFormFromModel($model, array(), $values, $form_config);
      F3::set('form', join(PHP_EOL, $form_markup_arr), false, false);
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Edit Record' action
  * 
  */
  public static function edit() {
    F3::clear('content');
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('edit')); }
    $title = 'Edit ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    $csrf_token = 'QOFJq34igj3'; //TODO: generate real token
    $record = CRUD::loadRecord($model);
    $record[0]->copyTo('record');
    if(!F3::exists('form')) {
      $model_path = CRUD_Helper::getModelPath();
      $form_config = array(
        'action' => $model_path . "/edit/${id}/token/{$csrf_token}/redirect/true",
        'method' => 'post',
      );
      $values = F3::get('record');
      $form_markup_arr = CRUD_Helper::buildFormFromModel($model, array(), $values, $form_config);
      F3::set('form', join(PHP_EOL, $form_markup_arr), false, false);
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Add and Edit Record POST/Processing' action
  * 
  */
  public static function addEditProcess() {
    $id = (int) F3::get('PARAMS.id') ?: null;
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //TODO: add form validation
    //TODO: validate csrf token (kohana version)

    function crud_load($model, $id) {
      $primary_key = Db_Meta::getPrimaryKeys($model);
      $record = CRUD::load_model($model);
      if($id > 0) { $record->load("{$primary_key}={$id}"); };
      return $record;
    }
    $record = crud_load($model, $id);
    //$allowed_fields = array_diff_key();
    
    //Allow namespacing of POST variables
    $post_model = 'POST.' . $model;
    if(F3::exists($post_model)) {
      $record->copyFrom($post_model);

      //Find related models and save them as well
      if(isset($record->related_models) && !empty($record->related_models)) {
        foreach($record->related_models as $related_model) {
          $post_related_models = 'POST.' . $related_model;
          if(F3::exists($post_related_models)) {
            foreach(F3::get($post_related_models) as $post_related_model) {
              $related_id = isset($post_related_model['id']) ? $post_related_model['id'] : 0;
              $related_record = crud_load($related_model, $related_id);
              if(!$related_record->dry()) { $record->save(); }
            }
          }
        }
      }
    } else {
      $record->copyFrom('POST'); //, $allowed_fields); //TODO: create blacklist of fields to not accept from POST      
    }

    //Update timestamp fields
    $now = date('Y-m-d H:i:s');
    if(!$id) { $record->created_at = $now; }
    $record->updated_at = $now;

    if(!$record->dry()) { $record->save(); }
    if(!F3::exists('PARAMS.redirect') || F3::get('PARAMS.redirect') !== 'true') { return true; }

    $record_id = $record->_id;
    $model_path = CRUD_Helper::getModelPath();
    $action = ($id > 0 && $record->id > 0) ?
      "<a href='{$model_path}/load/{$record->id}'>{$record->id}</a> updated" :
      "<a href='{$model_path}/load/{$record_id}'>{$record_id}</a> added";
    Notify::info("{$model_friendly} {$action} successfully.");
    F3::reroute($model_path);
  }


 /**
  *
  * 'Delete Record' action
  * 
  */
  public static function delete() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
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
      F3::set('form', $form, false, false);
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Delete Record POST/Processing' action
  *
  */
  public static function deleteProcess() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $model_path = CRUD_Helper::getModelPath();
    $delete_param = F3::get('POST.delete');
    //TODO: validate the CSRF token
    if($delete_param !== 'Yes') { 
      Notify::info("{$model_friendly} {$id} was not deleted.");
      if(!F3::exists('PARAMS.redirect') || F3::get('PARAMS.redirect') !== 'true') { return true; }
      F3::reroute($model_path);
      return;
    }
    $record = CRUD::loadRecord($model);
    $record[0]->erase();
    if(!F3::exists('PARAMS.redirect') || F3::get('PARAMS.redirect') !== 'true') { return true; }
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
    echo Export::render(CRUD::loadRecords('*', $limit, $page, false), $model);
  }


 /**
  *
  * 'HTML Select' Action - Echos ID/name value pairs for a given model as an HTML select element
  * 
  * This can be used in AJAX calls to populate the innerHTML of a DIV with the list of available model instances
  * 
  * @param array $config Form select element configuration
  * @param string $where SQL query WHERE clause items to limit model instances that are matched
  * @param string $order_by - SQL ORDER BY clause value that determined the order the records are returned in
  * 
  * @todo: Allow config to be passed in or read from GET params
  *
  */
  public static function optionlist($config = null, $where = '', $order_by = '') {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    //If where not provided, build one from $_GET parameters
    if(!$where) { $where = SQL::buildWhereFromArray($model, F3::get('GET')); }
    //TODO: move 'no selection/select' option to CRUD::pairs method
    $options = array('' => '(No Selection)') + CRUD::pairs($model, true, $where, $order_by);
    $config = $config ?: array(
      'id' => $model . '_id',
      'name' => $model . '_id',
    );
    $output = Form::select($model, $options, NULL, $config) . PHP_EOL;
    echo $output;
  }


 /**
  *
  * 'Search Records' action
  * 
  */
  public static function search() {
    F3::clear('content');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $records_name = Inflector::plural($model_friendly);
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('search')); }
    $title = 'Search ' . $records_name;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('form')) {
      $model_path = CRUD_Helper::getModelPath();
      $form_config = array(
        'action' => $model_path . "/searchresults",
        'method' => 'get',
      );
      $form_markup_arr = CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', join(PHP_EOL, $form_markup_arr), false, false);
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'Search Results' action
  * 
  */
  public static function searchResults() {
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('search')); }
    $title = $model_friendly . ' Search Results';
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('form')) {
      $model_path = CRUD_Helper::getModelPath();
      $form_config = array(
        'action' => $model_path . "/searchresults",
        'method' => 'get',
        'header' => 'Perform Another Search',
        'collapsed' => true
      );
      $form_markup_arr = CRUD_Helper::buildFormFromModel($model, array(), array(), $form_config);
      F3::set('form', join(PHP_EOL, $form_markup_arr), false, false);
    }
    $limit = F3::get('RECORDS_PER_PAGE');
    $page = (int) F3::get('GET.page') ?: 1;
    $records = CRUD::loadRecords('*', $limit, $page, false);
    if(!F3::exists('content')) { 
      if(!empty($records)) {
        F3::set('content', Export::render(CRUD_Helper::preprocessRecordData($records), $model, 'table'), false, false);
      } else {
        $records_name = Inflector::plural($model_friendly);
        F3::set('content', "No {$records_name} match the specified criteria.");
      }
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }


 /**
  *
  * 'View Record' action
  *
  */
  public static function view() {
    $id = (int) F3::get('PARAMS.id');
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    if(!F3::exists('navigation')) { F3::set('navigation', CRUD_Helper::navigation('view')); }
    $title = 'View ' . $model_friendly . ' ' . $id;
    if(!F3::exists('title')) { F3::set('title', $title); }
    if(!F3::exists('page_title')) { F3::set('page_title', $title . F3::get('PAGE_TITLE_BASE')); }
    if(!F3::exists('content')) { 
      $record = CRUD::loadRecord($model);
      $record[0]->copyTo('record');
      //TODO: fix issue with {{id}} / template tags being parse by F3 templater
      $record_content = F3::get('record');
      $field_values = array();
      foreach($record_content as $k => $v) {
        $field_values[] = array('Field' => String::humanize($k), 'Value' =>  nl2br(htmlentities($v)));
      }
      F3::set('content', Export::render($field_values, '', 'table'), false, false); 
    }
    CRUD_Helper::renderFlashMessages();
    echo Template::serve('layout.html');
  }

}
