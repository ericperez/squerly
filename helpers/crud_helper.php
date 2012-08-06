<?php 

class CRUD_Helper {
  
  //Adds the 'universal' prefix defined in the configuration to the model name
  //TODO: fix this
  public static function addTablePrefix($model) {
    return F3::get('DB_TABLE_PREFIX') . $model;
  }


  //Builds a basic HTML Form from a given model/database table based on it's structure
  //TODO: refactor this to use the depage-form model
  public static function buildFormFromModel($model, array $field_configs = array(), array $values = array(), $form_action, $form_method = 'post') {
    $output = Form::open($form_action, array('method' => $form_method));
    $output .= '<table><tbody>';
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


  //Builds a basic form for delete pages
  //TODO: this is a temporary method until something better is built
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


  //Gets the model param from the URI path and checks it against the model whitelist 
  //If it's in the whitelist it returns the model name/friendly name if found, otherwise sends 404 error
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


  //Determines the URL path for the current model
  public static function getModelPath() {
    list($model, $model_friendly) = self::getModelName();
    return F3::get('URL_BASE_PATH') . $model;
  }


  //Generates basic navigation array for a given action
  //TODO: refactor this; permissions, etc.
  //TODO: set 'action' as a param and retrieve from F3::get('PARAMS.action');
  //TODO: allow $extra_nav to override defaults
    //@param $action string - current CRUD 'action'
  //@param $extra_nav array - additional navigation actions
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


  //Runs preprocessing on an array of CRUD records (resolving foreign keys, etc.)
  public static function preprocessRecordData(array $records) {
    $records = Db_Meta::resolveForeignKeys($records);
    return $records;
  }


  //Converts SQL field types to their respective input field type
  //TODO: expand this
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