<?php
//TODO save configs in database for changes
//$configs = get_configuration_params();
$items_per_page = 25;
$redis = true;
$redis_cache_time = 60 * 60 * 24; //1 day default
$private = false;
$base_path = get_base_url(true) . '/';


//load override configuration


//DEFINE GLOBALBAR
define("REDIS",$redis);
define("REDIS_CACHE_TIME",$redis_cache_time);
define("PRIVATE_MODE",$private);
define("BASEPATH", $base_path);
define("MAX_ITEMS",$items_per_page);


