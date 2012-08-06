<?php

//@todo - Add Doc Blocks to all methods
class CRUD extends Axon {

  //CRUD Factory
  public static function delegate($model) {
    $class_name = String::modelToClass($model);
    if(@class_exists($class_name) && is_subclass_of($class_name, 'CRUD')) {
      return new $class_name();
    } else {
      return new self($model);
    }
  }


  //Load a single record using the AXON ORM
  //TODO: Clean this up
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


  //Load a collection of records using the AXON ORM
  public static function loadRecords($limit = 0, $page = 0, $use_default_model = false, $where = NULL) {
    list($model, $model_friendly) = CRUD_Helper::getModelName($use_default_model);
    $offset = ($page > 0 && $limit > 0) ? (int) ($page * $limit) - $limit : 0;
    $records = new Axon($model);
    $model_count = $records->found();
    if($offset > $model_count - ($limit - F3::get('RECORDS_PER_PAGE'))) { 
      $offset = $limit - F3::get('RECORDS_PER_PAGE');
    }
    $where = SQL::buildWhereFromArray($model, F3::get('GET'));
    return $records->afind($where, Db_Meta::getPrimaryKeys($model), $limit, $offset);
  }


  //Builds a key => name array from a given model
  public static function pairs($model, $order_by = 'pkey ASC') {
    $primary_key = Db_Meta::getPrimaryKeys($model);
    $name_field = Db_Meta::getNameColumn($model);
    if(empty($primary_key)) { F3::error('', "Unable to determine primary key for model {$model}"); }
    if(empty($name_field)) { F3::error('', "Unable to determine 'name' field for model {$model}"); }
    $sql = "SELECT {$primary_key} AS pkey, {$name_field} AS value FROM {$model} ORDER BY {$order_by}";
    return SQL::DBOptionlist($sql);
  }

}