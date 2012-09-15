<?php
/**
  *
  * Squerly - CRUD class
  * 
  * Squerly is built on top of the 'Fat-Free Framework (F3)' (@link http://bcosca.github.com/fatfree/)
  * F3 contains an amazing CRUD/ORM system named 'Axon' which automatically derives the model structure 
  * and properties based on the database table the model is sync'd to. This model extends the Axon class
  * and adds some supporting methods to allow delegation/factory-generation of Axon-based models and
  * figuring out which record/records to load based on GET parameters
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class CRUD extends Axon {

 /**
  *
  * CRUD Factory (makes CRUD haha)
  *
  * @param string $model CRUD model to instantiate
  * @return object Instance of CRUD-subclass specified in $model
  *
  */
  public static function delegate($model) {
    $class_name = String::modelToClass($model);
    if(@class_exists($class_name) && is_subclass_of($class_name, 'CRUD')) {
      return new $class_name();
    } else {
      return new self($model);
    }
  }


 /**
  *
  * Load a single record using the AXON ORM
  * 
  * This static method tried to determine the record's model + primary ID from $_GET and returns
  * a CRUD/Axon model object (in an array)
  *
  * @param string $model - Name of whitelisted DB table
  * @see Squerly.config.php - CRUD_TABLE_WHITELIST config item to see what tables are currently whitelisted
  * @see Crud_Controller
  * @return array Array with one element containing a CRUD Model instance
  *
  * @todo Clean this up
  * 
  */
  public static function loadRecord($model, $id = null) {
    $id = is_int($id) ? $id : (int) F3::get('PARAMS["id"]');
    if(empty($id)) { return null; }
    $table = CRUD_Helper::addTablePrefix($model);
    $primary_key = Db_Meta::getPrimaryKeys($table);
    if(empty($primary_key)) { F3::error('', 'Every table must have a primary key to be used with AXON ORM/CRUD'); }

    //Figure out if custom class exists for model; if so, instantiate that instead of CRUD
    $record = self::delegate($model);
    $record->load("{$primary_key} = {$id}");
    //Make sure record with id $id exists
    if($record->dry()) {
      $model_friendly = array_search($table, F3::get('CRUD_TABLE_WHITELIST'));
      Notify::error("{$model_friendly} {$id} does not exist.");
      F3::reroute(CRUD_Helper::getModelPath());
    }
    return array($record);
  }


 /**
  *
  * Loads a collection of records using the AXON ORM
  * 
  * This static method tried to determine the record's model + primary ID from $_GET and returns
  * a collection of CRUD/Axon model object (in an array)
  *
  * @param int $limit Maximum number of records to be returned in one call
  * @param int $page Pagination control (which 'page' of items is returns)
  * @param boolean $use_default_model if true, loads records from the 'default model' (@see squerly.config.php)
  * @param string $where SQL 'WHERE' clause (defaults to building a WHERE clause from $_GET if not explicitly set)
  * @see Squerly.config.php - RECORDS_PER_PAGE to see/configure the default records per page (pagination) value
  * @see Crud_Controller
  * @return array Array with multiple elements each containing a CRUD Model instance
  *
  * @todo Consolidate with 'loadRecord' ??
  * 
  */
  public static function loadRecords($limit = 0, $page = 0, $use_default_model = false, $where = NULL) {
    list($model, $model_friendly) = CRUD_Helper::getModelName($use_default_model);
    $offset = ($page > 0 && $limit > 0) ? (int) ($page * $limit) - $limit : 0;
    $records = new Axon($model);
    $model_count = $records->found();
    if($offset > $model_count - ($limit - F3::get('RECORDS_PER_PAGE'))) { 
      $offset = $limit - F3::get('RECORDS_PER_PAGE');
    }
    $where = !is_null($where) ? $where : SQL::buildWhereFromArray($model, F3::get('GET'));
    return $records->afind($where, Db_Meta::getPrimaryKeys($model), $limit, $offset);
  }


 /**
  *
  * Builds a SQL query from a given database table/model name ($model) which is passed to
  * SQL::DBOptionlist which returns a key/value array of records in that table.
  *
  * This method is very useful for building HTML SELECT option lists where ids/names for one DB table are needed.
  *
  * @param string $model - Name of whitelisted DB table
  * @param boolean $id_in_name - If true, the ID of the model will be prepended on the name
  * @param string $where SQL query WHERE clause items to limit model instances that are matched
  * @param string $order_by - SQL ORDER BY clause value that determined the order the records are returned in
  * @see SQL::DBOptionlist()
  * @return array Array containing key/value pairs for the specified table
  * 
  * @todo Switch 'order_by' to support arrays
  * 
  */
  public static function pairs($model, $id_in_name = false, $where = '', $order_by = 'pkey ASC') {
    $primary_key = Db_Meta::getPrimaryKeys($model);
    $name_field = Db_Meta::getNameColumn($model);
    if(empty($primary_key)) { F3::error('', "Unable to determine primary key for model {$model}"); }
    if(empty($name_field)) { F3::error('', "Unable to determine 'name' field for model {$model}"); }
    $where_clause = (empty($where)) ? '' : "WHERE {$where} ";
    $order_by_clause = (empty($order_by)) ? '' : "ORDER BY {$order_by} ";
    //TODO: Make sure this works on all DBs
    $db_type = F3::get("DB->backend");
    switch($db_type) {
      case 'pgsql':
      case 'sqlite':
        $sql = ($id_in_name) ? 
          "SELECT {$primary_key} AS pkey, '[' || {$primary_key} || '] ' || {$name_field} AS value FROM {$model} {$where_clause} {$order_by_clause}" :
          "SELECT {$primary_key} AS pkey, {$name_field} AS value FROM {$model} {$where_clause} {$order_by_clause}";
      break;

      case 'mysql':
      default:
        $sql = ($id_in_name) ? 
          "SELECT {$primary_key} AS pkey, CONCAT('[', {$primary_key}, '] ', {$name_field}) AS value FROM {$model} {$where_clause} {$order_by_clause}" :
          "SELECT {$primary_key} AS pkey, {$name_field} AS value FROM {$model} {$where_clause} {$order_by_clause}";
      break;
        
    }
    return SQL::DBOptionlist($sql);
  }

}