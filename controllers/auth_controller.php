<?php
/**
  *
  * Squerly - Auth Controller
  * 
  * This file contains all the additional routes needed for user authentication
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012 Squerly contributors (Eric Perez, et. al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  * @todo Flesh this out; it is currently unused
  * 
  */


class Auth_Controller {

  public static function authenticate() {
    //TODO: flesh this out
    F3::route('GET ' . F3::get('URL_BASE_PATH') . '/auth',
      function () {
        F3::set('AUTH',array('table'=>'user', 'id'=>'name', 'pw'=>'password'));
        $auth = Auth::basic('sql');
        if ($auth) {
          //set the session so user stays logged in
          F3::set('SESSION.user',$auth->name);
          F3::set('content','admin_home.html');
        } else {
          F3::set('content','security.html');
        }
        echo Template::serve('layout.html');  
      }
    );
  }

}