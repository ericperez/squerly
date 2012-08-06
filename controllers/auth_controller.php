<?php 

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