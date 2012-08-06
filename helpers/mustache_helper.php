<?php
//namespace squerly;

//TODO: autoload these
require __DIR__ . '/../vendor/Mustache/Autoloader.php';
require __DIR__ . '/../vendor/Mustache/Engine.php';
require __DIR__ . '/../vendor/Mustache/HelperCollection.php';
require __DIR__ . '/../vendor/Mustache/Context.php';
require __DIR__ . '/../vendor/Mustache/Template.php';
require __DIR__ . '/../vendor/Mustache/Compiler.php';
require __DIR__ . '/../vendor/Mustache/Tokenizer.php';
require __DIR__ . '/../vendor/Mustache/Parser.php';
require __DIR__ . '/../vendor/Mustache/Loader.php';
require __DIR__ . '/../vendor/Mustache/Loader/StringLoader.php';


class Mustache_Helper
{
  /**
   *
   * Returns an array of all template tags/substitution variables
   * @param $input string - 'tagged' input
   * @param $prefix_char boolean - adds a prefix to each returned variable
   * @return array - contains all 
   * @todo Allow custom tag openers/closers
   *
   */
  public static function vars($template, $prefix_char = null) {
    preg_match_all('/{\[(\S+)\]}/', $template, $matches);
    if(!$prefix_char) { return $matches[1]; }
    $output = array();
    foreach($matches[1] as $var) {
      $output[] = $prefix_char . $var;
    }
    return $output;
  }


  /**
   *
   * Static method to render a template using the mustache templating engine
   * @param $template string - 'tagged' input template
   * @param $vars array - array of tags => values to be substituted in $input
   * @return array - all the template tags contained in $template
   */
  public static function render($template, array $vars) {
    $mustache = new Mustache_Engine();
    return $mustache->render($template, $vars);
  }


  /**
   *
   * Static method to render a SQL template and generate an array of bound parameters
   * @param $template string - 'tagged' input template
   * @param 
   * @return arrays - SQL query with tags replaced with bound parameter placeholders / array of bound parameters
   *
   */
  public static function renderSQL($sql_template, $values) {
    $template_params = self::vars($sql_template, ':');
    $template_keys = self::vars($sql_template);
    $template_tags = array();
    foreach($template_keys as $tag) {
      $template_tags[$tag] = array_key_exists($tag, $values) ? $values[$tag] : '';
    }
    //TODO: remove lines with tags that are not required
    $template_vars = (sizeof($template_params) && sizeof($template_tags)) ? 
      array_combine($template_keys, $template_params) : array();
    $bound_sql = self::render($sql_template, $template_vars);
    return array($bound_sql, $template_tags);
  }


}