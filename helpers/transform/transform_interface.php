<?php
/**
  *
  * Squerly - Data Transformation Interface
  * 
  * This interface is implemented by all the 'transform' helper classes
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
interface Transform_Interface {
  
  public static function run(array $data, array $config = array());

}