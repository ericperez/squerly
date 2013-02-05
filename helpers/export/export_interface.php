<?php
/**
  *
  * Squerly - Export Interface
  * 
  * This interface is implemented by all the export helper classes
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
interface Export_Interface {
  
  public static function render(array $data, $filename = NULL, $configuration = array());
  //TODO: public static function setHeaders();
  //TODO: public static function isValid();
}