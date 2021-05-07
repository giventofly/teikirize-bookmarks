<?php /* api related functions */


/***
 * 
 * api calls
 * 
 */
function api_call($what=null){
  $method = strtolower($_SERVER['REQUEST_METHOD']);
  //$start_time = microtime(true);
  //default / nothing valid
  $response = ['error'=>'no access or invalid request!' ];
  //Check if private mode
  if(!PRIVATE_MODE || (PRIVATE_MODE && is_logged_in())){
    //get bookmarks
    if($method == 'get' && $what == 'bookmarks'){ 
      $sort_by_date = isset($_GET['sort']) && $_GET['sort'] == 'name' ? false : true;
      $order_desc = isset($_GET['order']) && $_GET['order'] == 'desc'? true : false;
      $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
      $query = isset($_GET['query']) ? $_GET['query'] : '';
      $tags = isset($_GET['tags']) && $_GET['tags'] ? explode(",", $_GET['tags']) : [];
      if(isset($_GET['query']) || !$tags){
        $bookmarks = get_bookmarks_from_query($sort_by_date,$order_desc,$page,$query);
      }
      else {
        $bookmarks = get_bookmarks_with_tag($sort_by_date,$order_desc,$page,$tags);
      }
      $response = [
        'tags'=> isset($_GET['withtags']) ? get_all_tags() : [],
        'bookmarks'=>$bookmarks['bookmarks'],
        'has_more'=> $bookmarks['has_more'],
        'params' => $_GET
      ];
    }
    //get all tags
    if($method == 'get' && $what == 'tags'){  
      $response = ['tags'=> get_all_tags()];
    }
  
  }
  //admin methods
  if($what=='manage' || $what=='export'){
    //only available if you're logged in
    if(!is_logged_in()){
      $response = ['error'=>'not logged in' ];
      //finish the request
      header('Content-type:application/json;charset=utf-8');
      echo json_encode($response);
      die();
    }
    //logged in
    if($method == 'post' && $what=='manage'){  
      /*
      //insert tag
      if(isset($_POST['insert_tag']) ){
        $name = isset($_POST['name']) ? $_POST['name'] : 'tag';
        $color = isset($_POST['color']) ? $_POST['color'] : '#030303';
        $response = insert_tag($name,$color);
      }
      */
      //edit tag
      if(isset($_POST['update_tag']) && 
         isset($_POST['tagid']) &&
         isset($_POST['newvalue'])
        ){
        $id = intval($_POST['tagid']);
        $name = $_POST['newvalue'];
        //TODO tag with specific color?
        //$color = isset($_POST['color']) ? $_POST['color'] : '#030303';
        $response = ['result'=> update_tag($id,$name)];
      }
      //delete tag
      if(isset($_POST['delete_tag']) && isset($_POST['id'])){
        $id = intval($_POST['id']);
        $response = delete_tag($id);
      }
      //delete bookmark
      if(isset($_POST['delete_bookmark']) && isset($_POST['uuid'])){
        $uuid = $_POST['uuid'];
        $response = delete_bookmark($uuid);
      }
      //insert bookmark
      if(isset($_POST['updateOrInsert']) && 
        $_POST['updateOrInsert'] == 'insert_bookmark' && 
        isset($_POST['url']) &&
        isset($_POST['title'])
        ){
          $url = $_POST['url'];
          $title = isset($_POST['title']) ? $_POST['title'] : '';
          $featimage = isset($_POST['feat_image']) ? $_POST['feat_image'] : '';
          $description = isset($_POST['description']) ? $_POST['description'] : '';
          $favicon = isset($_POST['favicon']) ? $_POST['favicon'] : '';
          $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
          $tags = isset($_POST['tags']) ? explode(",", $_POST['tags']) : [];
          $response = insert_bookmark($url,$title,$featimage,$description,$favicon,$notes,$tags);
          //$response['params'] = $_POST;
      }
      //edit bookmark
      if(isset($_POST['updateOrInsert']) && 
        $_POST['updateOrInsert'] == 'update_bookmark' && 
        isset($_POST['uuid'])
        ){
          $uuid = $_POST['uuid'];
          $title = isset($_POST['title']) ? $_POST['title'] : '';
          $featimage = isset($_POST['feat_image']) ? $_POST['feat_image'] : '';
          $description = isset($_POST['description']) ? $_POST['description'] : '';
          $favicon = isset($_POST['favicon']) ? $_POST['favicon'] : '';
          $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
          $tags = isset($_POST['tags']) ? explode(",", $_POST['tags']) : [];
          $response = update_bookmark($uuid,$title,$featimage,$description,$favicon,$notes,$tags);
          //$response['params'] = $_POST;
        }
      }
      //get media info
      if(isset($_POST['mediainfo']) && isset($_POST['url'])){
        $response = get_media_from_url($_POST['url']);
      }
      //export bookmarks
      if($method == 'get' && $what=='export'){
        $response = export_bookmarks();
      }
  }
  //finish the request
  header('Content-type:application/json;charset=utf-8');
  //$timetotal = round(((microtime(true) - $start_time)),5) . "s ";
  //$response['time_total'] = $timetotal;
  echo json_encode($response);
  die();
}


?>