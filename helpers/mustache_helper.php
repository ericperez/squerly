<?php
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

//TODO: namespace squerly;

class Mustache_Helper
{
  const EMPTY_TAG_PATTERN = "[[[**NO_VALUE**]]]";

  /**
   *
   * Returns an array of all template tags/substitution variables
   * 
   * @param string $template 'tagged' input
   * @param string $prefix adds a prefix to each returned variable
   * @param string $suffix adds a suffix to each returned variable
   * @return array Contains all the inside contents of the template tags with or without a prefix
   * 
   * @todo Allow custom tag openers/closers
   *
   */
  public static function vars($template, $prefix = '', $suffix = '') {
    preg_match_all('/{\[([A-Za-z0-9_]+)\]}/', $template, $matches);
    if($prefix === '' && $suffix === '') { return $matches[1]; }
    $output = array();
    foreach($matches[1] as $var) {
      $output[] = $prefix . $var . $suffix;
    }
    return $output;
  }


  /**
   *
   * Static method to render a template using the mustache templating engine
   * 
   * @param string $template 'Tagged' input template
   * @param array $vars Array of tags => values to be substituted in $input
   * @param string $callback Function name (or lambda function) to run each element in $vars through
   * @return string Template with Vars populated/substituted
   */
  public static function render($template, array $vars, $callback = null) {
    //Runs $callback method on every value in $vars that is a string (array $var values not currently supported)
    if(!empty($callback)) { $vars = array_map($callback, array_filter($vars, 'is_string')); }  
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
   *
   * @return object Object that was input as $object with properties run through template engine
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
   * Static method to render a SQL template and generate an array of bind parameters
   * 
   * @param string $sql_template 'Tagged' input template
   * @param array $values Associative array with values to fill into $sql_template
   * @param string $param_prefix Parameter replacement value prefix string (defaults to ':' which is what F3 is expecting)
   * @param string $param_suffix Parameter replacement value suffix string
   * @return array SQL query with tags replaced with bind parameter placeholders / array of bind parameters
   *
   */
  public static function renderSQL($sql_template, $values, $param_prefix = ':', $param_suffix = '') {
    $sql_template = SQL::stripComments($sql_template); //Strip comments off of SQL before template processing
    $template_keys = self::vars($sql_template);
    $template_params = self::vars($sql_template, $param_prefix, $param_suffix);
    $template_tags = array();

    //Figure out if values for each template tag were provided
    foreach($template_keys as $key) {
      $template_tags[$key] = (array_key_exists($key, $values)) ? $values[$key] : '';
    }

    //Create a combined array of keys and bind parameter placeholders
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

    //If there are duplicate template tag names within a query, they need to be made unique
    foreach(array_unique($template_keys) as $template_key) {
      $tag = $param_prefix . $template_key . $param_suffix;
      $num_keys = substr_count($bound_sql, $tag);
      for($i = $num_keys; $i > 1; $i--) {
        $new_key = String::numeralWords(mt_rand(0, 9) . '_' . ($i - 1)) . '_unique_' . $template_key;
        $new_tag = $param_prefix . $new_key . $param_suffix;
        $template_tags[$new_key] = $template_tags[$template_key]; //Assign new tag value of original key
        $bound_sql = String::str_replace_limit($tag, $new_tag, $bound_sql, 1);
      }
    }
    $bound_sql = trim($bound_sql, "\n\r\t ");
    return array($bound_sql, $template_tags);
  }

}
