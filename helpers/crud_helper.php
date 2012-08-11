<?php 
 /**
  *
  * Squerly - CRUD Helpers
  * 
  * The methods in CRUD_Helper are supporting functionality for the Squerly CRUD Controller & CRUD/Axon Models
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class CRUD_Helper {
  

 /**
  *
  * Adds the 'universal' prefix defined in the configuration to the model name
  *
  * @param string $model - DB Table/model name
  * @return string Prefix and Table name concatenated together
  * 
  * @todo Fix this! (Currently this does nothing)
  *
  */
  public static function addTablePrefix($model) {
    return F3::get('DB_TABLE_PREFIX') . $model;
  }


 /**
  *
  * Builds a basic HTML Form from a given model/database table based on it's structure
  *
  * @param string $model - DB Table/model name
  * @param array $field_configs Field configuration settings (HTML attributes)
  * @param array $values Mapping of field values
  * @param string $form_action URI string describing where the form values should be sent
  * @param string $form_method GET/POST/etc.
  * @return string HTML form that mirrors the data structure of DB table
  * 
  * @todo refactor this to use the depage-form model
  * 
  */
  public static function buildFormFromModel($model, array $field_configs = array(), array $values = array(), array $form_config = array()) {
    $form_header = isset($form_config['header']) ? '<h3>' . $form_config['header'] . '</h3><br/>' : '';
    $form_method = isset($form_config['method']) ? $form_config['method'] : 'post';
    $form_action = isset($form_config['action']) ? $form_config['action'] : '#'; //Post to current URI as default

    $output = Form::open($form_action, array('method' => $form_method));
    $output .= $form_header . '<table><tbody>';
    $table_desc = Db_Meta::describeTable($model);
    
    //Array of fields to not render in the form
    //TODO: expand this list
    $skip_fields = array('created_at', 'updated_at', 'edited_at', 'added_at', 'editstamp', 'updatestamp', 
      'edit_stamp', 'update_stamp', 'last_update', 'last_edit');

    $foreign_keys = Db_Meta::getForeignKeys($model);

    foreach($table_desc as $field) {
      if(in_array($field['COLUMN_NAME'], $skip_fields)) { continue; }
      $field_attribs = array(
        'name' => $field['COLUMN_NAME'],
        'id' => $field['COLUMN_NAME'],
        'type' => self::sqlFieldTypeMap($field['DATA_TYPE']),
        'maxlength' => $field['LENGTH'],
        //'size' => $field['LENGTH'],
        //'label' => String::humanize($field['COLUMN_NAME']),
      );

      //Set HTML5 'required' attribute (on POST forms)
      if(strtolower($form_method) === 'post' && !$field['NULLABLE']) { 
        $field_attribs['required'] = 'required'; 
      }

      //Make sure 'ID' fields are hidden
      //TODO: use table primary keys for $hidden_fields ??
      $hidden_fields = array('id', 'record_id', 'recordid', $field['TABLE_NAME'] . '_id', $field['TABLE_NAME'] . 'id'); //TODO: make this more robust
      if(in_array($field['COLUMN_NAME'], $hidden_fields)) { 
        $field_attribs['type'] = 'hidden'; 
        $output .= "<tr style='display: none;'><td>&nbsp;</td>\n";
      } else {
        //Build the Field Label
        $label = String::humanize($field['COLUMN_NAME']);
        $output .= '<tr><td>' . Form::label($field_attribs['name'], $label) . "</td>\n";
      }

      //Determine if a field is a foreign key; if so, get key/value pairs to the foreign table
      if(in_array($field['COLUMN_NAME'], $foreign_keys)) { $field_attribs['type'] = 'foreign_key'; }

      //Set the field values for existing records
      $field_attribs['value'] = (array_key_exists($field_attribs['name'], $values)) ? $values[$field_attribs['name']] : '';
            
      //TODO: consolidate some of this code and clean it up
      switch($field_attribs['type']) { //TODO: update to handle other field/element types
        case 'foreign_key':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array('' => '(No Selection)') + CRUD::pairs(Db_Meta::colToTable($field['COLUMN_NAME']));
          $output .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'boolean':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array('' => '(No Selection)', '1' => 'Yes', '0' => 'No');
          $output .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'select':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array(); //TODO: get option values from $values
          $output .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'textarea':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $output .= '<td>' . Form::textarea($field_attribs['name'], $value, $field_attribs) . "</td></tr>\n";
          break;

        default: //Handles number, date, datetime, time, and text fields
          $output .= '<td>' . Form::input($field_attribs['name'], $field_attribs['value'], $field_attribs) . "</td></tr>\n";
          break;
      }
    }
    $output .= '<tr><td>&nbsp;</td><td><br/>' . Form::submit('', 'Submit') . '</td></tr>';
    $output .= '</tbody></table>';
    $output .= Form::close() . "\n"; 
    return $output;
  }


 /**
  *
  * Builds a basic HTML form for CRUD delete routes
  *
  * @param string $model - DB Table/model name
  * @param string $id Primary key/ID value of record to delete
  * @return string HTML form that POSTs to the record 'delete' route
  * 
  * @todo this is a temporary method until something better is built
  * 
  */
  public static function buildDeleteForm($model, $id) {
    $model_path = self::getModelPath();
    $csrf_token = 'asdlfj4234oK'; //TODO: generate real token
    $form_action = "{$model_path}/delete/{$id}/token/{$csrf_token}";
    $output = 
      Form::open($form_action) . "\n" .
      Form::submit('delete', 'Yes') . '&nbsp;' . "\n" .
      Form::submit('delete', 'No') . "\n" . 
      Form::close() . "\n";
    return $output;
  }


 /**
  *
  * Gets the 'model' param from the URI path and checks it against the model whitelist
  * 
  * If it's in the whitelist the model name/friendly name is returned if found, otherwise sends 404 error
  *
  * @param boolean $use_default - If true, gets the default model from the DEFAULT_MODEL config item
  * @return array ('Table Name' => 'Friendly Name')
  * 
  */
  public static function getModelName($use_default = false) {
    $model = $use_default ? F3::get('DEFAULT_MODEL') : F3::get('PARAMS.model') ?: '';
    $table = !empty($model) ? CRUD_Helper::addTablePrefix($model) : '';
    $table = in_array($table, F3::get('CRUD_TABLE_WHITELIST')) ? $table : null;
    if(!$table) { 
      F3::error(404); exit;
    } elseif(!in_array($table, Db_Meta::getTables())) {
      F3::error('', "Configuration Error: Table '{$table}' does not exist!");
    }  else {
      return array($table, array_search($table, F3::get('CRUD_TABLE_WHITELIST')));
    }
  }


 /**
  *
  * Determines the URL path for the current model
  *
  * @return string 'URL_BASE_PATH' config item concatenated with the model name
  * 
  */
  public static function getModelPath() {
    list($model, $model_friendly) = self::getModelName();
    return F3::get('URL_BASE_PATH') . $model;
  }


 /**
  *
  * Generates basic navigation HTML for a given CRUD 'action/route'
  * 
  *
  * @param string $action string CRUD 'action/route'
  * @param $extra_nav array Additional navigation actions
  * @return string HTML that allows for basic navigation around the site
  * 
  * @todo refactor this; permissions, etc.
  * @todo set 'action' as a param and retrieve from F3::get('PARAMS.action');
  * @todo allow $extra_nav to override defaults
  */
  public static function navigation($action, array $extra_nav = array()) {
    $id = (int) F3::get('PARAMS.id') ?: null;
    list($model, $model_friendly) = CRUD_Helper::getModelName();
    $model_path = CRUD_Helper::getModelPath();
    $add_index = true;

    $nav_arr = array(
      'home' => array('Home' => F3::get('URL_BASE_PATH')),
      'index' => array('Back to Index' => $model_path),
      'add' => array("Add {$model_friendly}" => $model_path . "/add"),
      'edit' => array("Edit {$model_friendly} {$id}" => $model_path . "/edit/{$id}"),
      'delete' => array("Delete {$model_friendly} {$id}" => $model_path . "/delete/{$id}"),
      'view' => array("View {$model_friendly} {$id}" => $model_path . "/view/{$id}"),
    );

    $nav = array();

    switch($action) {
      case 'edit':
        $nav = array($nav_arr['view'], $nav_arr['delete']);
        break;

      case 'delete':
        $nav = array($nav_arr['view'], $nav_arr['edit']);
        break;

      case 'view':
        $nav = array($nav_arr['edit'], $nav_arr['delete']);
        break;

      case 'index':
        $add_index = false;
        $nav = array($nav_arr['add']);
        break;

      case 'add';
      case 'search':
      default:
        break;
    }
    if($add_index) { $nav = array_merge(array($nav_arr['index']), $nav); }
    $nav = array_merge(array($nav_arr['home']), $nav);
    if(!empty($extra_nav)) { $nav[] = $extra_nav; }
    $nav_html = array();
    foreach($nav as $item) {
      foreach($item as $label => $path) {
        $nav_html[] = HTML::anchor($path, $label);
      }
    }
    return '<div>' . join(' | ', $nav_html) . '</div><br/>'; //TODO: turn this into a list
  }


 /**
  *
  * Runs preprocessing on an array of CRUD records (resolving foreign keys + more later)
  *
  * @param array $records 2D array of CRUD record data
  * @return array 2D array of CRUD record data after processing
  * 
  */
  public static function preprocessRecordData(array $records) {
    $records = Db_Meta::resolveForeignKeys($records);
    return $records;
  }


 /**
  *
  * Converts SQL field types to their respective HTML input field type
  *
  * @param string $sql_type SQL field type to map
  * @return string Mapped HTML input field type
  * 
  * @todo Expand this
  * 
  */
  public static function sqlFieldTypeMap($sql_type) {
    $sql_type = preg_replace('/\(.*\)/', '', $sql_type);
    $field_map = array(
      'int'       => 'number',
      'tinyint'   => 'boolean',
      'boolean'   => 'boolean',
      'smallint'  => 'number',
      'mediumint' => 'number',
      'bigint'    => 'number',
      'decimal'   => 'number',
      'float'     => 'number',
      'double'    => 'number',
      'date'      => 'date',
      'datetime'  => 'datetime',
      'time'      => 'time',
      'year'      => 'number',
      'char'      => 'text',
      'varchar'   => 'text',
      'text'      => 'textarea',
    );
    return array_key_exists($sql_type, $field_map) ? $field_map[$sql_type] : 'text';
  }


}
