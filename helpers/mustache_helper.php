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
  const EMPTY_TAG_PATTERN = "[[[**NO_VALUE**]]]";

  /**
   *
   * Returns an array of all template tags/substitution variables
   * 
   * @param string $input 'tagged' input
   * @param boolean $prefix_char adds a prefix to each returned variable
   * @return array Contains all the inside contents of the template tags with or without a prefix
   * 
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
   * 
   * @param string $template 'Tagged' input template
   * @param array $vars Array of tags => values to be substituted in $input
   * @return string Template with Vars populated/substituted
   */
  public static function render($template, array $vars) {
    $mustache = new Mustache_Engine();
    return $mustache->render($template, $vars);
  }


  /**
   *
   * Static method to iterate over all of an object's properties and run them through
   *  the templating engine
   * 
   * @param object $object Object who's properties will be run through the templating engine
   * @param array $vars Array of tags => values to be substituted in $input
   */
  public static function renderObject(&$object, array $vars) {
    //Special case for Axon classes (due to 'magic' nature of properties)
    if(is_object($object) && is_subclass_of($object, 'Axon')) {
      $work_object = (object) $object->cast();
    } else {
      $work_object = $object;
    }

    $mustache = new Mustache_Engine();
    foreach($work_object as $key => $property) {
      if(is_string($property)) {
        $object->$key = $mustache->render($property, $vars); 
      }
    }
    return $object;
  }


  /**
   *
   * Static method to render a SQL template and generate an array of bound parameters
   * 
   * @param string $template 'Tagged' input template
   * @param array $values Associative array with values to fill into $sql_template
   * @return array SQL query with tags replaced with bound parameter placeholders / array of bound parameters
   *
   */
  public static function renderSQL($sql_template, $values) {
    $template_keys = self::vars($sql_template);
    $template_params = self::vars($sql_template, ':');
    $template_tags = array();

    //Figure out if values for each template tag were provided
    foreach($template_keys as $key) {
      $template_tags[$key] = (array_key_exists($key, $values)) ? $values[$key] : '';
    }

    //Create a combined array of keys and bound parameter placeholders
    $template_vars = (sizeof($template_keys) && sizeof($template_params)) ? 
      array_combine($template_keys, $template_params) : array();

    //Swap out any non-provided or empty-string values with a special identifier (for later removal)
    foreach($template_vars as $key => &$param) {
      $param = (!array_key_exists($key, $values) || $values[$key] === '') ? 
        self::EMPTY_TAG_PATTERN : $param;
    }

    //Remove lines from the SQL query that have template tags in them with no values
    $sql_lines = explode(PHP_EOL, self::render($sql_template, $template_vars));
    $bound_sql = '';
    foreach($sql_lines as $line) {
      if(strpos($line, self::EMPTY_TAG_PATTERN)) { continue; }
      $bound_sql .= $line . PHP_EOL;
    }
    return array($bound_sql, $template_tags);
  }


}
