<?php
/**
  *
  * Squerly - CRUD Controller Interface
  * 
  * This interface is implemented by the CRUD controller (which most other controllers should extend)
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  */
Interface Crud_Controller_Interface {

  public static function index();

  public static function add();

  public static function edit();

  public static function addEditProcess();

  public static function delete();

  public static function deleteProcess();

  public static function view();

  public static function search();

  public static function searchResults();

}