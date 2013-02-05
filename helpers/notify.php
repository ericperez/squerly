<?php
/**
  *
  * Squerly - Flash Message Notifications
  * 
  * This class is used to send notifications to the user when events happens that they need to be aware of
  * The notifications are saved in $_SESSION so they can persist between requests
  *
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  *
  * @todo add ability to persist messages through more than one 'hop'
  *
  */


class Notify {

  const SESSION_VAR = 'squerly_notification';
  const INVALID_TYPE_MSG = 'Invalid Message Type';
  static $msg_types = array('error', 'warning', 'info');

 /**
  *
  * Allows for shorthand static calls e.g. Notify::warning('foo');
  *
  * @param string $type @see Notify::$msg_types
  * @return boolean True on success; exception on failure
  *
  */
  public static function __callStatic($type, array $args) {
    return self::set($args[0], $type);
  }


 /**
  *
  * Sets a Flash message of type $type in session for later retrieval
  *
  * @param string $message The message that you want to 
  * @param string $type @see Notify::$msg_types
  * @return boolean True on success; exception on failure
  *
  */
  public static function set($message, $type = 'info') {
    if(!in_array($type, self::$msg_types)) { throw new exception(self::INVALID_TYPE_MSG); }
    if(!isset($_SESSION[self::SESSION_VAR])) { $_SESSION[self::SESSION_VAR] = array(); }
    if(!isset($_SESSION[self::SESSION_VAR][$type])) { $_SESSION[self::SESSION_VAR][$type] = array(); }
    $_SESSION[self::SESSION_VAR][$type][] = $message;
    return true;
  }


 /**
  *
  * Retrieves all the messages of type $type
  *
  * @param string $type @see Notify::$msg_types
  * @return mixed Message string if messages exist; null when none are available
  *
  */
  public static function get($type) {
    if(!isset($_SESSION[self::SESSION_VAR]) || !isset($_SESSION[self::SESSION_VAR][$type])) { return ''; }

    if(isset($_SESSION[self::SESSION_VAR]) && isset($_SESSION[self::SESSION_VAR][$type])) {
      $msg = $_SESSION[self::SESSION_VAR][$type];
      unset($_SESSION[self::SESSION_VAR][$type]); //clear out msgs after retrieving
      return $msg;
    }
    return null;
  }


 /**
  *
  * Returns HTML DIVs with the class set to 'flash_' . $type for display in a browser
  *
  * @param string $type @see Notify::$msg_types
  * @return string HTML output (DIVs containing the flash messages)
  *
  */
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


 /**
  *
  * Loops through all types in Notify::$msg_types and returns HTML DIVs for each msg/type
  *
  * @see Notify::render()
  * @return string HTML output (DIVs containing the flash messages)
  *
  */
  public static function renderAll() {
    $output = '';
    foreach(self::$msg_types as $type) {
      $output .= self::render($type);
    }
    return $output;
  }


 /**
  *
  * Prevents the class from being instantiated--all of it's methods should be called statically
  *
  */
  final private function __construct() {}

}
