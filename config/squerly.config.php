<?php
//TODO: Move all (or most) of this into it's own .ini configuration file.

/*
  DB INIT EXAMPLES:
  SQLite - F3::set('DB',new DB('sqlite:path/test.sqlite'));
  MySQL - F3::set('DB',new DB('mysql:host=localhost [;port=port] [;dbname=testdb]','userid','password'));
  MongoDB - F3::set('DB',new MongoDB(new Mongo('mongodb://localhost [:port ]'),'testdb'));
  Jig - F3::set('DB', new FileDB('path/'));
*/

//Database Setup
F3::set('DB',
  new DB(
    'mysql:host=localhost;port=3306;dbname=blog', //Change this to match your DB
    'root', //Username
    '' //Password
  )
);

//Fat-Free Framework Setup Variables
F3::set('DEBUG', 3); //Debug output verbosity
F3::set('UI', 'views/'); //Path to Views
F3::set('AUTOLOAD', 'lib/; models/; controllers/; helpers/; forms/; vendor/; vendor/php_kml/;');
F3::set('IMPORTS', 'views/');
F3::set('CACHE', FALSE); //Enable or disable caching globally
//F3::set('PLUGINS', '');

//Squerly Setup Variables
F3::set('PAGE_TITLE_BASE', ' - Squerly(tm)'); //Base 'HEAD' title for HTML pages
F3::set('URL_BASE_PATH', '/'); //Relative base path for all Squerly requests
F3::set('RECORDS_PER_PAGE', 1000); //Number of records to display per page on multi-record views
F3::set('DEFAULT_MODEL', 'report');
F3::set('CSRF_EXPIRY_HOURS', 8); //Number of hours the CSRF tokens remain valid @todo - Implement
F3::set('DB_TABLE_PREFIX', ''); //Used in case DB tables have a common prefix (which wont be used on the URI path) @todo - Implement
F3::set('REPORT_CACHE_EXPIRE', '60'); //Number of seconds to cache SQL-based Report results @todo - Expand to emcompass all reports

//Locale Setup Variables
putenv('LC_ALL=en_US');
setlocale(LC_ALL, 'en_US');
date_default_timezone_set('America/Denver'); //Change this to your own timezone

//Array of data models that may be accessed through the CRUD routing interface
//Format: 'Friendly Name' => 'model name'
F3::set('CRUD_TABLE_WHITELIST', array(
  'Report' => 'report', 
  //'Report Configuration' => 'report_config', 
  //'Report Set' => 'report_set',
  'Test Table' => 'test_table', //TODO: tmp
  'User' => 'user',  //TODO: tmp
  'Article' => 'article',  //TODO: tmp
));

