<?php


/** Define ABSPATH as this file's directory */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

//load 
require_once(ABSPATH . '/core/functions.php');

/** check if first run */
if ( file_exists( ABSPATH . '/firstrun' ) ) {
  define("FIRSTRUN",true);
  $ssl = is_ssl() ? 'https://' : 'http://';
  if($ssl . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] != get_base_url(). '/manage/first-run'){
    header('Location: '. get_base_url() . '/manage/first-run');
    exit;
  }
}

$routes = [
  //404
  'pathNotFound' => null,
  //method not allowed
  'methodNotAllowed' => null,
  'routes' => [
    //API
    [
      'method' => ['get', 'post'],
      //'function' => 'methodNotAllowed',
      'function' => 'api_call',
      'expression' => '/api/(bookmarks|tags|manage|export)?'
    ],
    //first run
    [
      //'method' => ['get','post'],
      'method' => ['get','post'],
      'expression'=> '/manage/first-run',
      'function' => 'load_first_run',
    ],
    //admin management
    [
      'method' => ['get','post'],
      //'method' => 'get',
      'expression'=> '/manage',
      'function' => 'load_admin_template',
    ],
    //root
    [
      //'method' => ['get','post'],
      'method' => 'get',
      'expression'=> '/',
      'function' => 'load_root_template',
    ]
  ],
];

//load routes
routes($routes);




