<?php /* core functionalities */

/**
 * Determines if SSL is used.
 *
 * from wordpress code
 * 
 * @return bool True if SSL, otherwise false.
 */
function is_ssl() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}

		if ( '1' == $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}
/**
 * Guess the URL for the site.
 *
 * original from wordpress code (wp_guess_url)
 * @param bool base url path or full url
 * @return string The guessed URL.
 */

function get_base_url($just_basepath=false,$tail='') {
	$abspath_fix = str_replace( '\\', '/', ABSPATH );
	$script_filename_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );

	if ( $script_filename_dir . '/' === $abspath_fix ) {
			// Strip off any file/query params in the path.
			$path = preg_replace( '#/[^/]*$#i', '', $_SERVER['PHP_SELF'] );
		} 
	else {
			if ( false !== strpos( $_SERVER['SCRIPT_FILENAME'], $abspath_fix ) ) {
				// Request is hitting a file inside ABSPATH.
				$directory = str_replace( ABSPATH, '', $script_filename_dir );
				// Strip off the subdirectory, and any file/query params.
				$path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '', $_SERVER['REQUEST_URI'] );
			} elseif ( false !== strpos( $abspath_fix, $script_filename_dir ) ) {
				// Request is hitting a file above ABSPATH.
				$subdirectory = substr( $abspath_fix, strpos( $abspath_fix, $script_filename_dir ) + strlen( $script_filename_dir ) );
				// Strip off any file/query params from the path, appending the subdirectory to the installation.
				$path = preg_replace( '#/[^/]*$#i', '', $_SERVER['REQUEST_URI'] ) . $subdirectory;
			} else {
				$path = $_SERVER['REQUEST_URI'];
			}
	}
	$schema = is_ssl() ? 'https://' : 'http://'; // set_url_scheme() is not defined yet.
	$url    = $just_basepath ? $path : $schema . $_SERVER['HTTP_HOST'] . $path;
	return rtrim( $url, '/' );
}
/**
 * converts text for a friendly url
 *
 * @param string text
 *
 * @return string text friendly url
 * 
 */
function slugify($text){
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  // transliterate
  $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  // trim
  //$text = trim($text, '-');
  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);
  // lowercase
  $text = trim(strtolower($text));

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}
/**
 * 
 * To help some debuging
 * 
 * @param arr/string
 * @return echo pre code with arr/string
 * 
 */
function debugme(...$arr){
  foreach ($arr as $key => $value) {
    echo "<pre>".print_r($value,true)."</pre>";
  }
}
/**
 * 
 * @param string url to get
 * @param bool use proxy
 * @return string html page
 * 
 */
//this could be optimized a bit
function getURL($url,$proxy=false){
  //  Initiate curl
  $ch = curl_init();
  $z = rand(0,30);
  $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.' . $z;
  curl_setopt($ch, CURLOPT_USERAGENT, $agent);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // Set the url
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  //headers
  //*** PROXY */
  if($proxy){
    $username = '';
    $password = '';
    $session = mt_rand();
    $username = $username . "-session-" . $session;
    $port = 22225;
    $super_proxy = '';
    curl_setopt($ch, CURLOPT_PROXY, "http://$super_proxy:$port");
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$username-country-us-session-$session:$password");
  }
  // Execute
  //curl_setopt($ch, CURLOPT_VERBOSE, 1); 
  $response = curl_exec($ch);
  $err = curl_error($ch);
  if(curl_errno($ch)) {
    $response['error'] = 'Curl error: '.curl_error($ch);
  }
  $info = curl_getinfo($ch);
  if($info['http_code'] == 429){
    $response['error'] = 'Curl error: too many requests\n<br>';
  }
  // Closing
  curl_close($ch);
  //echo "geturl: $response<br>";
  //if($err) { return $err; }
  return $response;
}
/**
 * 
 * get media info from url
 * 
 * @param string url
 * @return arr 
 *  string title
 *  string description
 *  string keywords
 *  string featured_image
 *  string favicon
 *  arr string images_src
 * 
 */
function get_media_from_url($url=""){
  $output = [
    'title' => '',
    "description" => '',
    "keywords" => '',
    "featured_image" => '',
    "favicon" => '',
    'images_src' => [],
  ];
  if(!filter_var($url, FILTER_VALIDATE_URL,FILTER_FLAG_HOST_REQUIRED) || !preg_match("#^https?://.+#", $url)){
    return $output;
  }
  //vars
  $title = "";
  $description = "";
  $keywords = "";
  $feat_image = "";
  $favicon = "";
  $image_src = [];
  //get page
  $html = getURL($url);
  if(isset($html['error'])){ return $output; }
  // a new dom object
  $dom = new domDocument; 
  libxml_use_internal_errors(true);
  // load the html into the object
  $dom->loadHTML($html); 
  //no error output
  $errors = libxml_get_errors();
  libxml_clear_errors();
  // discard white space
  $dom->preserveWhiteSpace = false;
  //title
  $title_dom = $dom->getElementsByTagName('title');
  $title = $title_dom[0]->nodeValue;
  //get <meta>s
  $metas = $dom->getElementsByTagName('meta');
  foreach ($metas as $meta) {
    //description or replace when og:description exists
    if ($meta->getAttribute('name') == 'description' || $meta->getAttribute('property') == 'og:description') {
      $description = $meta->getAttribute('content');
    }
    //keywords
    if ($meta->getAttribute('name') == 'keywords') {
      $keywords = $meta->getAttribute('content');
    }
    //featured image
    if ($meta->getAttribute('property') == 'og:image') {
      $feat_image = $meta->getAttribute('content');
      $image_src[] = $feat_image;
    }
  }
  //favicon
  $favicons = $dom->getElementsByTagName('link');
  foreach ($favicons as $link) {
    //use first favicon only
    if(!$favicon && $link->getAttribute('rel') == 'icon' || $link->getAttribute('rel') == 'shortcut icon'){
      $favicon = $link->getAttribute('href');
    }
  }
  //get all images from page
  $images = $dom->getElementsByTagName('img');
  for ($i = 0; $i < $images->length; $i ++) {
      $image = $images->item($i);
      $src = $image->getAttribute('src'); 
      if(!filter_var($src, FILTER_VALIDATE_URL) || !preg_match("#^https?://.+#", $src)){
        $image_src[] = $src;
      }
  }
  //if no featured imaged is set
  if(!$feat_image && $images->length){
    $feat_image = $image_src[0];
  }
  //return media page info
  $output = [
    'title' => $title,
    "description" => $description,
    "keywords" => $keywords,
    "featured_image" => $feat_image,
    "favicon" => $favicon,
    'images_src' => $image_src,
    //'body' => $body
  ];
  return $output;
}
/**
 * 
 * calculate x time ago from date
 * @param int
 * @return string
 * 
 */
function time_ago($time=1616414328){
  //$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
  //$lengths = array("60","60","24","7","4.35","12","10");
  //no weeks
  $periods = array("second", "minute", "hour", "day", "month", "year", "decade");
  $lengths = array("60","60","24","31","12","10");
  $now = time();
  $difference = $now - $time;
  for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
      $difference /= $lengths[$j];
  }
  $difference = round($difference);
  if($difference != 1) {
      $periods[$j].= "s";
  }
  return $difference . ' ' . $periods[$j] . ' ago';
}
/**
 * 
 * @return array php/configuration info
 * 
 */
function get_server_info(){
  $bt_stats = get_bookmarks_tags_stats();
  $info = [
    "php version" => phpversion(),
    "REDIS available" => class_exists('Redis'),
    "use REDIS" => REDIS,
    "REDIS CACHE TIME" => REDIS_CACHE_TIME,
    "PRIVATE MODE" => PRIVATE_MODE,
    "BASEPATH" => BASEPATH,
    "base url" => get_base_url(),
    "MAX ITEMS" => MAX_ITEMS,
    "TOTAL BOOKMARKS" => $bt_stats['total_bookmarks'],
    "TOTAL TAGS" => $bt_stats['total_tags'],
  ];
  return $info;
}
/**
 * 
 * export bookmarks as a json file
 * @return json 
 * 
 * 
 */
function export_bookmarks(){
  $has_more = true;
  $page = 0;
  $bookmarks = [];
  $all_tags = get_all_tags();
  while($has_more){
    $current_query = get_bookmarks_from_query(true,true,$page,''); 
    //get tags name from tag id
    foreach($current_query['bookmarks'] as $bookmark_key => $bookmark){
      foreach($bookmark['tags'] as $t => $tag_id){
        //this should not be a loop, but this would be rarely used and I don't want to spend time doing additional queries
        foreach($all_tags['list'] as $tag_key => $tag){
          if($tag['tag_id'] == $tag_id){
            $current_query['bookmarks'][$bookmark_key]['tags'][$t] = $tag['tag'];
          }
        }
      }
      
    }
    $bookmarks = array_merge($bookmarks,$current_query['bookmarks']);
    $page++;
    $has_more = $current_query['has_more'];
  }
  return $bookmarks;
}
/**
 * 
 * @param string path of .json to load
 * @return arr [number of inserted items, failed items]
 * 
 */
function import_bookmarks(){
  $inserted = 0;
  $fail = [];
  //verify file exists - it has to exist at this point, shouldn't be called without verifying it first - but lets be sure
  if ( !file_exists( ABSPATH . '/databases/bookmarks.json' ) ) { return 0; }
  //read file and insert
  $file = file_get_contents(ABSPATH . '/databases/bookmarks.json', FILE_USE_INCLUDE_PATH);
  $to_import = json_decode($file,true);
  if($to_import == NULL) { 
    switch (json_last_error()) {
      case JSON_ERROR_NONE:
          echo ' - No errors';
      break;
      case JSON_ERROR_DEPTH:
          echo ' - Maximum stack depth exceeded';
      break;
      case JSON_ERROR_STATE_MISMATCH:
          echo ' - Underflow or the modes mismatch';
      break;
      case JSON_ERROR_CTRL_CHAR:
          echo ' - Unexpected control character found';
      break;
      case JSON_ERROR_SYNTAX:
          echo ' - Syntax error, malformed JSON';
      break;
      case JSON_ERROR_UTF8:
          echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
      break;
      default:
          echo ' - Unknown error';
      break;
    }
    return -1;
  }
  foreach ($to_import as $key => $bookmark) {
    //small validation
    if(isset($bookmark['url']) &&
      isset($bookmark['title']) &&
      isset($bookmark['image']) &&
      isset($bookmark['description']) &&
      isset($bookmark['favicon']) &&
      isset($bookmark['notes']) &&
      isset($bookmark['tags'])) {
        //try to insert
        $result = insert_bookmark($bookmark['url'],$bookmark['title'],$bookmark['image'],$bookmark['description'],$bookmark['favicon'],$bookmark['notes'],$bookmark['tags']);
        //already there or other error
        if($result != -1 && $result != 0){
          $inserted++;
        }
        else {
          $fail[] = $bookmark; 
        }
    }
    else {
      $fail[] = $bookmark; 
    }
  }
  //remove file
  unlink(ABSPATH . '/databases/bookmarks.json');
  //in case of fail, replace file with the failled ones
  if($fail){
    //file_put_contents ( ABSPATH . '/bookmarks.json', json_encode($fail));
  }
  return ['inserted'=>$inserted,'fail'=>$fail];
}
/**
 * 
 * @param int seconds
 * @return string time by days,hours,minutes,seconds
 * 
 */
function secondsToTime($seconds) {
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

