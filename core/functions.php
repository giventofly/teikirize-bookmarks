<?php /* load all functions parts */
global $auth;
//load vendor
require_once(ABSPATH . 'vendor/autoload.php');
//core
require_once(ABSPATH . '/core/core.php');
//database
require_once(ABSPATH . '/core/db.php');
//auth functions
require_once(ABSPATH . '/core/auth.php');
//do auth
$auth = askAuth();
//load config
require_once(ABSPATH . '/core/configs.php');
//bookmarks
require_once(ABSPATH . '/core/bookmarks.php');
//api
require_once(ABSPATH . '/core/api.php');
//routing
require_once(ABSPATH . '/core/routes.php');
//views
require_once(ABSPATH . '/core/views.php');

