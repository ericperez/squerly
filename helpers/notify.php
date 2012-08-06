<?php

//'Flash' message/notification class
//TODO: add ability to persist messages through more than one hop
class Notify {

  const SESSION_VAR = 'squerly_notification';
  const INVALID_TYPE_MSG = 'Invalid Message Type';
  static $msg_types = array('error', 'warning', 'info');

  //Allows for shorthand static calls e.g. Notify::warning('foo');
  public static function __callStatic($type, array $args)
  {
    return self::set($args[0], $type);
  }


  //Sets a Flash message of type $type
  public static function set($message, $type = 'info') {
    if(!in_array($type, self::$msg_types)) { throw new exception(self::INVALID_TYPE_MSG); }
    if(!isset($_SESSION[self::SESSION_VAR])) { $_SESSION[self::SESSION_VAR] = array(); }
    if(!isset($_SESSION[self::SESSION_VAR][$type])) { $_SESSION[self::SESSION_VAR][$type] = array(); }
    $_SESSION[self::SESSION_VAR][$type][] = $message;
  }


  //
  public static function get($type) {
    if(!isset($_SESSION[self::SESSION_VAR]) || !isset($_SESSION[self::SESSION_VAR][$type])) { return ''; }

    if(isset($_SESSION[self::SESSION_VAR]) && isset($_SESSION[self::SESSION_VAR][$type])) {
      $msg = $_SESSION[self::SESSION_VAR][$type];
      unset($_SESSION[self::SESSION_VAR][$type]); //clear out msgs after retrieving
      return $msg;
    }
    return null;
  }


  //Returns HTML DIVs with the class set to 'flash_' . $type
  public static function render($type) {
    if(!in_array($type, self::$msg_types)) { throw new exception(self::INVALID_TYPE_MSG); }
    $output = '';
    $msgs = self::get($type);
    if(empty($msgs)) { return ''; }
    foreach($msgs as $msg) {
      $output .= "<div class='{$type}'>{$msg}</div><br/>" . PHP_EOL;
    }
    return $output;
  }


  //Loops through all types in self::$msg_types and returns HTML DIVs for each msg/type
  public static function renderAll() {
    $output = '';
    foreach(self::$msg_types as $type) {
      $output .= self::render($type);
    }
    return $output;
  }


  //Don't allow instantiation
  final private function __construct()
  {
  }


}