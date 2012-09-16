<?php
/**
  *
  * Squerly - Report Database Connection Model
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  */
class Report_DB_Connection {


 /**
  *
  * Loads the Database Connections from db_connection.json into F3 registry variables
  * 
  */
  public static function loadAll() {
    require_once __DIR__ . '/../lib/filedb.php';
    $dbc_model = new Jig('db_connections.json', new FileDB('config', 2)); //TODO: make DB_Connection extend Jig ??
    $db_connections = $dbc_model->afind();
    //Load the DB connections from db_connections.json into the framework registry
    foreach($db_connections as $dbc) {
      //Skip invalid connection strings
      $dbc_properties = array('name', 'type', 'connection_string', 'username', 'password');
      $name = $dbc['name'];
      foreach($dbc_properties as $dbc_property) {
        if(!isset($dbc[$dbc_property])) { continue 2; }
      }
      F3::set("DB_{$name}", new DB($dbc['connection_string'], $dbc['username'], $dbc['password']));
    }
  }

}
