<?php
/**
  *
  * Squerly - Main class to farm out the task of transforming 2D array data
  * 
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  *
  */
class Transform  {


 /**
  *
  * Attempts to determine the type of data transformation the user requested
  * 
  * Uses $transformation if passed in, otherwise looks at $_REQUEST['sqrl']['transform'], and lastly return an empty string
  * 
  * @param string $transformation Data transformation type override
  * @return string Name of transformation plugin
  * 
  */
  public static function getTransformation($transformation = '') {
    $request_transformation = isset($_REQUEST['sqrl']['transform']) ? $_REQUEST['sqrl']['transform'] : '';
    return $transformation ?: String::modelToClass($request_transformation) ?: '';
  }


 /**
  *
  * Enumerates all the data transformation plugins and returns an an array of their names
  * 
  * @return array of data transformation plugins in format 'short_name' => 'friendly name'
  * 
  */
  public static function pairs() {
    //TODO: Automate this...
    return array(
      ''                      => 'None',
      'velocity'              => 'Instant Velocity',
      'minmaxmean'            => 'Min/Max/Mean Values',
      'sum'                   => 'Sum of All Values',
      'mean'                  => 'Mean Average of All Values',
      'round'                 => 'Round off all Decimal Points',
      'min'                   => 'Minimum Values',
      'max'                   => 'Maximum Values',
      'abs'                   => 'Absolute Values',
      'ceil'                  => 'Round All Values Up',
      'floor'                 => 'Round All Values Down',
      'cumulative_sum'        => 'Cumulative Sums',
    );
  }


 /**
  *
  * Calls a data transformation plugin with the input 2D data array
  * 
  * @param array $data 2D array of data to be transformed
  * @param string $transformation_type Determines which transformation helper gets called (defaults to no transformation)
  * @param array $config Array of configuration settings
  *
  * @return Outputs the result of the transformation plugin
  *
  */
  public static function run($data, $transformation_type = '', array $config = array()) {
    if(!$data) { $data = array(array()); }
    $transformation_type = $transformation_type ?: self::getTransformation($transformation_type);
    if(!$transformation_type) { return $data; }
    $transform_plugin = 'Transform_' . $transformation_type;
    $class_implements = @class_implements($transform_plugin) ?: array(); //Get plugin interfaces
    if(@class_exists($transform_plugin) && in_array('Transform_Interface', $class_implements)) {
      return $transform_plugin::run($data, $config);
    } else {
      F3::error('', 'Invalid data transformation plugin or plugin not found.');
    }
  }



}