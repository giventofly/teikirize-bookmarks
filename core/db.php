<?php /* database and redis functions */

//REDIS managmenet could be improved for sure
//** make redis connection
function getRedis(){
  if(!REDIS || !class_exists('Redis')){ return null; }
  $redis = new Redis();
  try {
    $connect = $redis->connect('127.0.0.1', 6379, 0.25);
    if(!$connect) { return null; }
    //$redis->ping();
    $redis->select(1);	
  }catch (RedisException $ex) {
    error_log($ex.PHP_EOL);
    return null;
  }
  return $redis;
}
//** check if redis key is set
function getRedisKey($type='main',$sub=''){
  $redis = getRedis();
  if($redis){
    $key = $type . ":" . md5($sub);
    //debugme($key);
    //debugme($redis->exists("$key"));
    // If $key exists get and unserialize it, otherwise set $data to empty array
    if($redis->exists($key)) {
      //debugme($redis->exists($key));
      $values = unserialize($redis->get($key));
      $redis->close();
      return $values;
    } 
  }
  return [];
}
//** set value for redis key
function setRedisKey($type='main',$sub='',$values=[],$ttl=0){
  $redis = getRedis();
  if($redis){
    $key = $type . ":" . md5($sub);
    if($ttl > 0){
      //ttl in seconds
      //echo "setting with set and expire";
      $redis->set($key, serialize($values));                  
      $redis->expire($key, $ttl); 
      //debugme(getRedisKey('main',$sub));
    }
    else {
      $redis->set($key, serialize($values));
      $redis->expire($key,60*60*24*7);
    }
    $redis->close();
  }
}
//** redis bust memory values from type
function bustRedisKeys($type='main'){
  //**** main:*, users:*, static:* ******/
  $redis = getRedis();
  if($redis){ 
    return $redis->del($redis->keys($type . ':*')); 
    $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
    $it = NULL;
    while ($keys = $redis->scan($it, "$type:*")) {
        foreach ($keys as $key){
            $redis->del($key);
        }
    }
    $redis->close();
  }
}
/**
 * 
 * Executes database queries
 * 
 * @param string sql query
 * @param bool true for create,update,delete,insert, false for select
 * @return object result of query
 * 
 */
function askBD($sql='',$params=[]){
	$db = new SQLite3(ABSPATH . '/databases/bookmarks.sqlite') or die('Unable to open database');
  //execute
  $stmt = $db->prepare($sql);
  if($params){
    foreach ($params as $id => $value) {
      # code...
      $stmt->bindValue($id, $value);
    }
  }
  $result = $stmt->execute();
  return [$db,$result];
}
/**
 * 
 * mixed of mysql bind param to SQLite3 params
 * s = string, i = integer, d = double,  b = blob  
 * @param string sif
 * @return SQLite3 constant
 * 
 */
//not used
function convert_param_to_sqlite3($string){
  /*
  https://www.php.net/manual/en/sqlite3stmt.bindparam.php
  SQLITE3_INTEGER: The value is a signed integer, stored in 1, 2, 3, 4, 6, or 8 bytes depending on the magnitude of the value.
  SQLITE3_FLOAT: The value is a floating point value, stored as an 8-byte IEEE floating point number.
  SQLITE3_TEXT: The value is a text string, stored using the database encoding (UTF-8, UTF-16BE or UTF-16-LE).
  SQLITE3_BLOB: The value is a blob of data, stored exactly as it was input.
  SQLITE3_NULL: The value is a NULL value.
  */
}


