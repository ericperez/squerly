<?php
/**
 *
 * Squerly - CRUD Helpers
 *
 * The methods in CRUD_Helper are supporting functionality for the Squerly CRUD Controller & CRUD/Axon Models
 *
 * @author Eric Perez <ericperez@squerly.net>
 * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
 * @license GNU General Public License, version 3 or later
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.squerly.net
 *
 */
class CRUD_Helper {

  static $model_whitelist = array(
    'Report' => 'report',
    'Saved Report Configuration' => 'saved_report',
    'Form Field Tooltip' => 'form_field_tooltip',
    'Schedule' => 'schedule',
    'Email Sender' => 'email_sender',
    'Email Recipient' => 'email_recipient',
    'Email Distribution List' => 'email_distribution_list',
    'Email Distribution List Recipient' => 'email_distribution_list_recipient',
    'Emailed Report Event' => 'event_email_saved_report_results',
    'Data Source Type' => 'data_source_type',
    'Data Source' => 'data_source',
    'SSH Connection' => 'ssh_connection',
    'Saved Report Group' => 'saved_report_group',
    'Saved Report Group Element' => 'saved_report_group_element',
  );

  /**
   *
   * Adds 'action' columns to a record dataset
   *
   * @param array $records 2D Array of CRUD records
   * @param array $additional_actions Array
   * @return array Records with 'action' columns added
   *
   */
  public static function addActionColumns(array $records, array $additional_actions = array()) {
    list($model, $model_friendly) = self::getModelName();
    $class_name = String::modelToClass($model);

    //For now, it's assumed that primary key is called 'id'
    if(!isset($records[0]['id'])) { return $records; }

    foreach($records as &$record) {
      $id = $record['id'];
      //TODO: find a faster way of doing this
      //$additional_actions = array();
      if(class_exists($class_name)) { //TODO: && implements interface that defines getIndexActions!
        $additional_actions = @$class_name::getIndexActions($record) ?: array();
      }
      $model_path = self::getModelPath();
      $actions = array(
        array("Load"    => "<a href='{$model_path}/load/{$id}'>Load</a>"),
        array("Edit"    => "<a href='{$model_path}/edit/{$id}'>Edit</a>"),
        array("Copy"    => "<a href='{$model_path}/copy/{$id}'>Copy</a>"),
        array("Delete"  => "<a href='{$model_path}/delete/{$id}'>Delete</a>"),
        array("Details" => "<a href='{$model_path}/view/{$id}'>View</a>"),
      );
      if(!empty($additional_actions)) { array_unshift($actions, $additional_actions); }
      foreach($actions as $action) {
        $record = $record + $action;
      }
    }
    return $records;
  }


  /**
   *
   * Adds the 'universal' prefix defined in the configuration to the model name
   *
   * @param string $model DB Table/model name
   * @return string Prefix and Table name concatenated together
   *
   * @todo Fix this! (Currently this method does nothing)
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
   * @param array $form_config Configuration including URI string describing where the form values should be sent
   * @param string Database connection variable name
   * @return string HTML form that mirrors the data structure of DB table
   *
   * @todo refactor this to use the depage-form model
   * @todo separate table markup from form markup
   *
   */
  public static function buildFormFromModel($model, array $field_configs = array(), array $values = array(), array $form_config = array(), $DBC = 'DB') {
    $form_header = isset($form_config['header']) ? '<h3>' . $form_config['header'] . '</h3><br/>' : '';
    $form_method = isset($form_config['method']) ? $form_config['method'] : 'post';
    $form_action = isset($form_config['action']) ? $form_config['action'] : '#'; //Post to current URI as default
    $output = array();
    $output['form_header_markup'] = Form::open($form_action, array('method' => $form_method));
    $output['form_header_markup'] .= $form_header . "<table class='datatable'><thead></thead><tbody>"; //TODO: add fields to thead!!!!
    $table_desc = Db_Meta::describeTable($model, $DBC);

    //Array of fields to not render in the form
    //TODO: expand this list
    $skip_fields = array(); //'created_at', 'updated_at', 'edited_at', 'added_at', 'editstamp', 'updatestamp', 
    //'edit_stamp', 'update_stamp', 'last_update', 'last_edit', 'last_edited_at', 'last_updated_at');

    $foreign_keys = Db_Meta::getForeignKeys($model);
    $tooltips = self::getTooltipHTML($model);

    foreach($table_desc as $field) {
      $column_name = $field['COLUMN_NAME'];
      if(in_array($column_name, $skip_fields)) { continue; }

      $field_attribs = array(
        'name' => $model . '[' . $column_name . ']', //Namespace using model name
        'id' => $model . '_' . $column_name . '_input',
        'type' => self::sqlFieldTypeMap($field['DATA_TYPE'], $field['LENGTH']),
        'maxlength' => is_null($field['LENGTH']) ? null : (int) $field['LENGTH'],
        'title' => isset($tooltips[$column_name]) ? $tooltips[$column_name] : '',
        //'size' => $field['LENGTH'],
      );

      //Set HTML5 'required' attribute (on POST forms)
      if(strtolower($form_method) === 'post' && !$field['NULLABLE']) {
        $field_attribs['required'] = 'required';
      }

      //Make sure 'ID' fields are hidden
      //TODO: use table primary keys for $hidden_fields ??
      //TODO: make this more robust
      $hidden_fields = array(
        'id',
        'record_id',
        'recordid',
        $field['TABLE_NAME'] . '_id',
        $field['TABLE_NAME'] . 'id',
        'created_by_user_id', //TODO: update these fields on back end instead of form
        'created_at',
        'updated_by_user_id',
        'updated_at',
        'deleted_by_user_id',
        'deleted_at',
      );
      if(in_array($field['COLUMN_NAME'], $hidden_fields)) {
        $field_attribs['type'] = 'hidden';
        $output[$column_name] = "<tr style='display: none;'><td>&nbsp;</td>\n";
      } else {
        //Build the Field Label
        $label_text = String::humanize($field['COLUMN_NAME']);
        $output[$column_name] = '<tr><td style="white-space:nowrap; width:1%">' . Form::label($field_attribs['id'], $label_text, array('title' => $field_attribs['title'])) . "</td>\n";
      }

      //Determine if a field is a foreign key; if so, get key/value pairs to the foreign table
      if(in_array($field['COLUMN_NAME'], $foreign_keys)) { $field_attribs['type'] = 'foreign_key'; }

      //Set the field values for existing records
      $field_attribs['value'] = (array_key_exists($column_name, $values)) ? $values[$column_name] : '';

      //TODO: consolidate some of this code and clean it up
      switch($field_attribs['type']) { //TODO: update to handle other field/element types
        case 'foreign_key':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array('' => '(No Selection)') + CRUD::pairs(Db_Meta::colToTable($field['COLUMN_NAME']), true);
          $output[$column_name] .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'boolean':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array('' => '(No Selection)', '1' => 'Yes', '0' => 'No');
          $output[$column_name] .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'select':
          $value = $field_attribs['value'];
          unset($field_attribs['value'], $field_attribs['type']);
          $options = array(); //TODO: get option values from $values
          $output[$column_name] .= '<td>' . Form::select($field_attribs['name'], $options, $value, $field_attribs) . "</td></tr>\n";
          break;

        case 'textarea':
          $value = $field_attribs['value'];
          $max_height = 300;
          $min_height = 100;
          $height = $field_attribs['maxlength'] / 12;
          $height = ($height >= $min_height) ? $height : $min_height;
          $height = ($height <= $max_height) ? $height : $max_height;
          $field_attribs['style'] = "width: 800px; height: {$height}px;";
          unset($field_attribs['value'], $field_attribs['type']);
          $output[$column_name] .= '<td>' . Form::textarea($field_attribs['name'], $value, $field_attribs) . "</td></tr>\n";
          break;

        default: //Handles number, date, datetime, time, and text fields
          $value = $field_attribs['value'];
          if($field_attribs['type'] !== 'hidden') {
            $min_width = 50;
            $max_width = 400;
            $width = $field_attribs['type'] === 'number' ? $min_width : $field_attribs['maxlength'] * 10;
            $width = ($width >= $min_width) ? $width : $min_width;
            $width = ($width <= $max_width) ? $width : $max_width;
            $field_attribs['style'] = "width: {$width}px;";
          }
          $output[$column_name] .= '<td>' . Form::input($field_attribs['name'], $value, $field_attribs) . "</td></tr>\n";
          break;
      }
    }
    $output['form_footer_markup'] = '<tr><td>&nbsp;</td><td><br/>' . Form::submit('', 'Submit') . "</td></tr>\n";
    $output['form_footer_markup'] .= '</tbody></table>'. Form::close() . "<br><br>\n";
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
    $redirect = 'true'; //TODO: read this from 'redirect' PARAM
    $form_action = "{$model_path}/delete/{$id}/token/{$csrf_token}/redirect/{$redirect}";
    $output =
      Form::open($form_action) . "\n" .
        Form::submit('delete', 'Yes') . '&nbsp;' . "\n" .
        Form::submit('delete', 'No') . "\n" .
        Form::close() . "\n";
    return $output;
  }


  /**
   *
   * Retrieves form markup based on a PHP class name
   *
   * @param string $class PHP class name that contains the form configuration info
   * @return string Form markup
   *
   */
  public static function getForm($class) {
    if(is_null($class)) { return null; }
    $class_implements = (@class_exists($class) && @class_implements($export_plugin)) ?: array();
    if(!in_array('Form_Interface', $class_implements)) { F3::error('', "Form class must implement 'Form_Interface'"); }
    //TODO: finish this...
  }


  //TODO: Add method getInstanceName()

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
    $uri_path = explode('/', F3::get('PARAMS.0'));
    $model = $use_default ? F3::get('DEFAULT_MODEL') : F3::get('PARAMS.model') ?: $uri_path[2] ?: '';
    $table = !empty($model) ? self::addTablePrefix($model) : '';
    $table = in_array($table, F3::get('CRUD_TABLE_WHITELIST')) ? $table : null;
    if(!$table) {
      ob_clean();
      F3::error('', 'Could not determine model name or table is not in the whitelist');
    } elseif(!in_array($table, Db_Meta::getTables())) {
      F3::error('', "Configuration Error: Table '{$table}' does not exist!");
    }  else {
      return array($table, array_search($table, F3::get('CRUD_TABLE_WHITELIST')));
    }
  }


  /**
   *
   * Gets the application 'instance' param from the URI path
   *
   * @param boolean $use_default - If true, gets the default model from the DEFAULT_MODEL config item
   * @return string Application Instance Name
   *
   */
  public static function getInstanceName($use_default = false) {
    $uri_path = explode('/', F3::get('PARAMS.0'));
    $instance = $use_default ? F3::get('DEFAULT_INSTANCE') : F3::get('PARAMS.instance') ?: $uri_path[1] ?: '';
    return $instance;
  }


  /**
   *
   * Determines the URL path for the current model
   *
   * @return string 'URL_BASE_PATH' config item concatenated with the model name
   *
   */
  public static function getModelPath() {
    list($model) = self::getModelName();
    $instance = self::getInstanceName();
    return F3::get('URL_BASE_PATH') . $instance . '/' . $model;
  }


  /**
   *
   * Fetches form field tooltips for all fields by table name and returns them as an array
   *
   * @param string $table_name CRUD Database table name
   * @return array Associative array in the format $field_name => $tooltip_html
   *
   */
  public static function getTooltipHTML($table_name) {
    $tooltip_file = __DIR__ . "/tooltips/{$table_name}.json";
    if(!file_exists($tooltip_file)) { return array(); }
    return json_decode(file_get_contents($tooltip_file), true);
  }


  /**
   *
   * Normalizes boolean-esque data into
   *
   * @param array $records 2D array of CRUD record data
   * @return array 2D array of CRUD record data after processing
   *
   */
  public static function normalizeBooleanFields(
    array $records,
    array $bool_cols = array(),
    $true_val = 'Yes',
    $false_val = 'No'
  ) {
    $columns = array_keys($records[0]);
    foreach($columns as $column) {
      $col_data = Matrix::pick($records, $column);

    }
    return $records;
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
    list($model, $model_friendly) = self::getModelName();
    $model_plural = Inflector::plural($model_friendly);
    $model_path = self::getModelPath();

    //TODO: This should be able to read the CRUD routes and be generated from that
    $basic_nav = array(
      'home'    => array('Home' => F3::get('URL_BASE_PATH')),
      'index'   => array('Back to Index' => $model_path),
      'add'     => array("Create New {$model_friendly}" => $model_path . "/add"),
      'search'  => array("Search {$model_plural}" => $model_path . "/search"),
    );

    $record_nav = array(
      'copy'    => array("Copy {$model_friendly} {$id}" => $model_path . "/copy/{$id}"),
      'delete'  => array("Delete {$model_friendly} {$id}" => $model_path . "/delete/{$id}"),
      'edit'    => array("Edit {$model_friendly} {$id}" => $model_path . "/edit/{$id}"),
      'load'    => array("Load {$model_friendly} {$id}" => $model_path . "/load/{$id}"),
      'view'    => array("View {$model_friendly} {$id} Details" => $model_path . "/view/{$id}"),
    );

    //Remove unwanted navigation items depending on what action is currently
    if(in_array($action, array_keys($record_nav)) || !in_array($action, array_keys($basic_nav))) {
      $nav = $basic_nav + $record_nav + $extra_nav;
      unset($nav[$action]);
    } else {
      $nav = $basic_nav + $extra_nav;
    }
    if($action === 'index') { unset($nav['index']); }
    $nav_html = array();
    foreach($nav as $item) {
      foreach($item as $label => $path) {
        $nav_html[] = HTML::anchor($path, $label);
      }
    }
    return "<div id='navigation_div'>&nbsp;" . join('&nbsp;|&nbsp;', $nav_html) . '</div>'; //TODO: turn this into a list
  }


  /**
   *
   * Runs pre-processing on an array of CRUD records (resolving foreign keys + more later)
   *
   * @param array $records 2D array of CRUD record data
   * @return array 2D array of CRUD record data after processing
   *
   * @todo Pass $records by reference ??
   *
   */
  public static function preprocessRecordData(array $records) {
    //XSS mitigation
    foreach($records as &$record) {
      foreach($record as &$val) {
        $val = htmlentities($val);
      }
    }
    //TODO: pass $records by reference
    $records = Db_Meta::resolveForeignKeys($records);
    $records = self::addActionColumns($records);
    $records = self::relativizeDateColumns($records);
    //$records = self::normalizeBooleanFields($records);
    return $records;
  }


  /**
   *
   * Converts SQL field types to their respective HTML input field type
   *
   * @param string $sql_field_type SQL field type to map
   * @param integer|null $length Maximum length of data in field
   *
   * @return string Mapped HTML input field type
   *
   * @todo Expand this
   *
   */
  public static function sqlFieldTypeMap($sql_field_type, $length = null) {
    $sql_field_type = preg_replace(array('/[^a-z]/', '/\(.*\)/'), '', strtolower($sql_field_type));
    $field_map = array(
      'int'       => 'number',
      'tinyint'   => 'boolean',
      'boolean'   => 'boolean',
      'bool'      => 'boolean',
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
    $text_types = array('char', 'varchar');
    $max_text_length = 255;
    if(in_array($sql_field_type, $text_types) && $length > $max_text_length) {
      $return = 'textarea';
    } else {
      $return = array_key_exists($sql_field_type, $field_map) ? $field_map[$sql_field_type] : 'text';
    }
    return $return;
  }


  /**
   *
   * Changes the Created, Updated and Deleted At Columns to relative dates
   *
   * @param array $records 2D array of CRUD record data
   * @return array 2D array of CRUD record data after processing
   *
   * @todo Pass $records by reference ??
   *
   */
  public static function relativizeDateColumns(array $records) {
    $date_fields = array('created_at', 'updated_at'); //, 'deleted_at'); //TODO: update this list
    foreach($records as &$record) {
      foreach($date_fields as &$date_field) {
        $record[$date_field] = Date_Difference::getString(date_create($record[$date_field]));
      }
    }
    return $records;
  }



  /**
   *
   * Returns an array of the 'base' set of JavaScript files used on most pages
   *
   * @return array JavaScript file URIs
   *
   * @todo Clean this up
   *
   */
  public static function getBaseJavascript() {
    return array(
      "//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.1/jquery.min.js",
      "//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js",
      "//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/jquery.dataTables.min.js",
      "//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/js/TableTools.min.js",
      "//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/js/ZeroClipboard.min.js",
    );
  }


  /**
   *
   * Returns an array of the 'base' set of CSS files used on most pages
   *
   * @return array CSS file URIs
   *
   * @todo Clean this up
   *
   */
  public static function getBaseStylesheets() {
    return array(
      "//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/css/base/jquery-ui.css",
      "//cdnjs.cloudflare.com/ajax/libs/jquery.ui/1.8.22/themes/smoothness/jquery-ui.css",
      "//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/css/jquery.dataTables.css",
      "//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/css/TableTools.min.css",
    );
  }


}
