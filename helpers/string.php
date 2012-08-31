<?php
/**
  *
  * Squerly - String Helper class
  * 
  * Contains methods to help with manipulating strings
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
class String {
  
 /**
  *
  * 'Humanize' a string formatted for a machine (e.g. 'field_name' -> 'Field Name')
  *
  * @param string $input Input string to format
  * @return string 'Human-friendly' string
  *
  * @todo Make this more robust
  * 
  */
  public static function humanize($input) {
    $replacements = array(
      '/ Id$/'       => '',
      '/Id/'         => 'ID',
      '/^Row/'       => '',
      '/Html/'       => 'HTML',
      '/Css/'        => 'CSS',
      '/Ssl/'        => 'SSL',
      '/Uri/'        => 'URI',
      '/Url/'        => 'URL',
      '/Ui/'         => 'UI',
      '/Db/'         => 'DB',
      '/Javascript/' => 'JavaScript',
    );
    $output = $input;
    $output = ucwords(strtolower(str_replace('_', ' ', $output)));
    $output = preg_replace(array_keys($replacements), array_values($replacements), $output);
    return $output;
  }


 /**
  *
  * Strip spaces and uppercase from a string to make it 'machine-friendly' e.g. for database field names
  *
  * @param string $input Input string to format
  * @param boolean $uc_words If true, the returned value has words upper-cased
  * @return string 'Machine/DB-friendly' string
  * 
  */
  public static function machine($input, $uc_words = false) {
    return preg_replace(array('/ /', '/[^0-9a-zA-Z_]/'), array('_', ''), 
      ($uc_words) ? ucwords(strtolower($input)) : strtolower($input));
  }


 /**
  *
  * Strips 'C'-style comments off of a string (single line and multiline)
  *
  * @param string $input Input string to format
  * @return string Input string with comments stripped off
  * 
  */
  public static function stripComments($input) {
    return trim(preg_replace('/((\/\/).*)|(\/\\*).*(\*\/)/', '', $input));
  }


 /**
  *
  * Turns a model/table name into a class name
  *
  * @param string $input Input string to format
  * @return string Input string with comments stripped off
  * 
  */
  public static function modelToClass($model) {
    return str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($model))));
  }


 /**
  *
  * Replaces all single numeric digits in a string with their spelled-out counterpart
  *
  * @param string $input Input string to format
  * @return string Input string with numerals converted to words
  * 
  */
  public static function numeralWords($input) {
    $number_words = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
    return str_replace(array_keys($number_words), $number_words, (string) $input);
  }


 /**
  *
  * Prevents the class from being instantiated--all of it's methods should be called statically
  *
  */
  final private function __construct() {}


}