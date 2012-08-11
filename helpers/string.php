<?php

class String {
  
  //'Humanize' a machine-friendly string
  //TODO: Make this more robust
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


  //Strip spaces and uppercase from a string to make it 'machine-friendly' e.g. for database field names
  //@param $input string - Input to convert
  //@param $uc_words boolean - If true, the returned value has words upper-cased
  public static function machine($input, $uc_words = false) {
    return preg_replace(array('/ /', '/[^0-9a-zA-Z_]/'), array('_', ''), 
      ($uc_words) ? ucwords(strtolower($input)) : strtolower($input));
  }


  //Strips 'C'-style comments off of a string (single line and multiline)
  public static function stripComments($input) {
    return trim(preg_replace('/((\/\/).*)|(\/\\*).*(\*\/)/', '', $input));
  }


  //Turns a model/table name into a class name
  public static function modelToClass($model) {
    return str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($model))));
  }


 /**
  *
  * Prevents the class from being instantiated--all of it's methods should be called statically
  *
  */
  final private function __construct() {}


}