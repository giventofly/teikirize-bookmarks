<?php


/**
 * 
 * insert tag (allow duplicate tags)
 * @param string name of tag
 * @param string hexcolor of tag
 * @return int tag id on sucess, 0 otherwise
 * 
 */
//color not used, maybe in the future
function insert_tag($name='tag',$color='#030303'){
  $params = [
    ':tag_slug' => slugify($name),
    ':tag' => $name,
    ':color' => $color
  ];
  $sql = "INSERT INTO tags 
            ('tag_slug','tag','color') 
          VALUES 
            (:tag_slug,:tag,:color)";
  [$db,$result] = askBD($sql,$params);
  if($db->lastInsertRowID()){
    if(REDIS){
      bustRedisKeys('tags');
      bustRedisKeys('tag_name_');
    }
    return $db->lastInsertRowID();
  }
  return 0;
}
/**
 * 
 * edits tag
 * @param int tag id
 * @param string name of tag
 * @param string hexcolor of tag
 * @return int tag id on sucess, 0 otherwise
 * 
 */
function update_tag($tag_id=0,$name='tag',$color='#030303'){
	$params = [
    ':tag_slug' => slugify($name),
    ':tag' => $name,
    ':color' => $color,
    ':tag_id' => intval($tag_id)
  ];
  $sql = "UPDATE tags 
          SET tag = :tag, tag_slug = :tag_slug ,color = :color 
          WHERE tag_id = :tag_id";
  [$db,$result] = askBD($sql,$params);
  if($result){
    if(REDIS){
      bustRedisKeys('tags');
      bustRedisKeys('tag_name_');
    }
    return intval($tag_id);
  }
  return 0;
}
/**
 * 
 * gets tag id from name
 * @param string name of tag
 * @return int tag id on sucess, 0 otherwise
 * 
 */
function get_tag_from_name($name='tag'){
  if(REDIS){
    $tag_id = getRedisKey('tag_name_',$name);
    if($tag_id){ return $tag_id; }
  }
	$params = [
    ':tag' => $name,
  ];
  $sql = "SELECT tag_id
            FROM tags
          WHERE tag = :tag";
  [$db,$result] = askBD($sql,$params);
  while ($row = $result->fetchArray()){
    if(REDIS){
      setRedisKey('tag_name_',$name,$row['tag_id']);
    }
    return $row['tag_id'];
  }
  return 0;
}
/**
 * 
 * deletes tag
 * @param int tag id
 * @return bool true on sucess, false otherwise
 * 
*/
function delete_tag($tag_id){
	$params = [
    ':tag_id' => intval($tag_id)
  ];
  $sql = "DELETE 
            FROM tags 
          WHERE tag_id = :tag_id";
  [$db,$result] = askBD($sql,$params);
  if(REDIS){
    bustRedisKeys('tags');
    bustRedisKeys('tag_name');
  }
  return boolval($result);
}
/**
  * inserts bookmark
  * @param string url, required
  * @param string title, required
  * @param string featured image link
  * @param string description
  * @param string favicon link
  * @param string notes
  * @param arr list of tag ids
  * @return string bookmark uuid
*/
function insert_bookmark($url=null,$title=null,$feat_image='',$description='',$favicon='',$notes='',$tags=[]){
  if(!filter_var($url, FILTER_VALIDATE_URL) || !preg_match("#^https?://.+#", $url)){
    return -1;
  }
  if(bookmark_exists($url)){ return -2; }
  $uuid = \Delight\Auth\Auth::createUuid();
  $params = [
    ':url' => $url,
    ':title' => $title,
    ':feat_image' => $feat_image,
    ':description' => strip_tags($description),
    ':favicon' => $favicon,
    ':notes' => $notes,
    ':uuid' => $uuid,
    ':date' => time()
  ];
  $sql = "INSERT INTO bookmarks 
            ('url','image','description','title','bookmark_uuid','favicon','notes', 'date')
          VALUES 
            (:url, :feat_image, :description, :title, :uuid, :favicon, :notes, :date)";
  [$db,$result] = askBD($sql,$params);
  //insert tags
  $bookmark_id = 0;
  if($db->lastInsertRowID()){
    $bookmark_id = $db->lastInsertRowID();
  }
  else {
    return 0;
  }
  $tags_id = [];
  //get tag ids or insert new tags
  foreach ($tags as $key => $value) {
    if($value){
      $tag_id = get_tag_from_name($value);
      if($tag_id){
        $tags_id[] = $tag_id;
      }
      else {
        $tag_id = insert_tag($value);
        if($tag_id){
          $tags_id[] = $tag_id;
        }
      }
    }
  }
  insert_tags_bookmark($tags_id,$uuid);
  if(REDIS){
    bustRedisKeys('bookmarks');
  }
  return $bookmark_id;
}
/**
  *
  * bookmarks exists
  * @param string url
  * @param string
  *
*/
function bookmark_exists($url=null){
  $params = [':url',$url];
  $sql = "SELECT bookmark_uuid FROM bookmarks where url = :url";
  [$db,$result] = askBD($sql,$params);
  while ($row = $result->fetchArray()){
    return $row['bookmark_uuid'];
  }
  return '';
}
/**
  *
  * delete bookmarks
  * @param string uuid
  * @param bool
  *
*/
function delete_bookmark($bookmark_uuid=null){
  $params = [
    ':uuid' => $bookmark_uuid
  ];
  $sql = "DELETE from bookmarks 
          WHERE bookmark_uuid = :uuid";
  [$db,$result] = askBD($sql,$params);
  //remove tags associated
  delete_tags_bookmark($bookmark_uuid);
  if(REDIS){
    bustRedisKeys('bookmarks');
  }
  return boolval($result);
}
/**
  * edits bookmark
  * @param string bookmark uuid, required
  * @param string title, required
  * @param string featured image link
  * @param string description
  * @param string favicon link
  * @param string notes
  * @param arr list of tag ids
  * @return string bookmark uuid
*/
function update_bookmark($uuid=null,$title=null,$feat_image='',$description='',$favicon='',$notes='',$tags=[]){
  $params = [
    //':url' => $url,
    ':title' => $title,
    ':feat_image' => $feat_image,
    ':description' => strip_tags($description),
    ':favicon' => $favicon,
    ':notes' => $notes,
    ':uuid' => $uuid,
    ':date' => time()
  ];
  $sql = "UPDATE bookmarks 
          SET 'image'=:feat_image,'description'=:description,'title'=:title,'favicon'=:favicon,'notes'=:notes, 'date'=:date
          WHERE bookmark_uuid = :uuid";
  [$db,$result] = askBD($sql,$params);
  //tags
  $tags_id = [];
  //get tag ids or insert new tags
  foreach ($tags as $key => $value) {
    if($value){
      $tag_id = get_tag_from_name($value);
      if($tag_id){
        $tags_id[] = $tag_id;
      }
      else {
        $tag_id = insert_tag($value);
        if($tag_id){
          $tags_id[] = $tag_id;
        }
      }
    }
  }
  insert_tags_bookmark($tags_id,$uuid);
  if(REDIS){
    bustRedisKeys('bookmarks');
  }
  return $uuid;
}
/**
  * @param string bookmark uuid
  * @param arr tag ids to insert/edit
  * @return int error, or number of inserted
*/
function insert_tags_bookmark($tags=[],$bookmark_uuid=null){
  if(!$bookmark_uuid){ return -1; }
  delete_tags_bookmark($bookmark_uuid);
  //lets assume no malicious intent and tag_ids all exist
  $inserted = 0;
  foreach ($tags as $tag_id) {
    $sql = "INSERT INTO bookmarks_tags
                    ('tag_id','bookmark_uuid')
            VALUES (:tag_id, :bookmark_uuid)";
    $params = [':tag_id'=>$tag_id,'bookmark_uuid'=>$bookmark_uuid];
    askBD($sql,$params);
    $inserted++;
  }
  return $inserted;
}
/**
  * deletes tags bookmarks
  * @param string bookmark uuid
  * @return bool
*/
//this exists because it just easier when updating tags to remove all and insert the news ones
function delete_tags_bookmark($bookmark_uuid){
  $params = [
    ':uuid' => $bookmark_uuid
  ];
  $sql = "DELETE from bookmarks_tags
          WHERE bookmark_uuid = :uuid";
  [$db,$result] = askBD($sql,$params);
  if(REDIS){
    bustRedisKeys('tags');
    bustRedisKeys('tag_name');
  }
  return boolval($result);
}
/**
  * get all tags info
  * @return arr tags info ['name','slug','id','color]
*/
function get_all_tags(){
  if(REDIS){
    $tags = getRedisKey('tags');
    if($tags){ return $tags; }
  }
  $sql = "SELECT tag_id,tag_slug,tag,color 
          FROM tags
          ORDER by tag ASC";
  [$db,$result] = askBD($sql);
  $tags = [];
  $index = 0;
  $by_index = [];
  while ($row = $result->fetchArray()){
    $tags['list'][] =  [
      'tag_id' => $row['tag_id'],
      'tag_slug' => $row['tag_slug'],
      'tag' => $row['tag'],
      'color'=> $row['color']
    ];
    $tags['index'][$row['tag_id']] = $index;
    $index++;
  }
  if(REDIS){
    setRedisKey('tags','',$tags);
  }
  return $tags;
}
/**
  * get all bookmarks info
  * @param bool order by [name,date]
  * @param bool sort by [asc,desc]
  * @param int page [0,...]
  * @param string text to search (title, description, notes)
  * 
  * @return arr [bookmarks=> bookmarks info, has_more => bool]
*/
function get_bookmarks_from_query($sort_by_date=true,$order_desc=true,$page=0,$query=''){
  if(REDIS){
    $bookmarks = getRedisKey('bookmarks','query_'.$sort_by_date.'_'.$order_desc.'_'.$page.'_'.$query);
    if($bookmarks){ return $bookmarks; }
  }
  //order by
  $sql_orderby = $sort_by_date ? "ORDER BY bookmarks.date " : " ORDER BY bookmarks.title ";
  $sql_orderby_2 = $sort_by_date ? "ORDER BY bt.date " : " ORDER BY bt.title ";
  //sort by
  if($sort_by_date){
    $sql_orderby .= !$order_desc ?  " DESC " : " ASC  ";
    $sql_orderby_2 .= !$order_desc ?  " DESC " : "  ASC ";
  }
  else {
    $sql_orderby .= $order_desc ?  " DESC " : " ASC  ";
    $sql_orderby_2 .= $order_desc ?  " DESC " : "  ASC ";
  }

  //offset
  $max = MAX_ITEMS * intval($page);
  //limit
  $limit = MAX_ITEMS;
  $bookmarks = [];
  $params = [];
  //where
  $sql_where = "  ";
  if($query){
    $query = "%$query%";
    $sql_where .= "WHERE bookmarks.title like  :title  
                  OR bookmarks.description like :description 
                  OR bookmarks.url like :url
                  OR bookmarks.notes like :notes";
    $params = [
      ':title' => $query,
      ':description' => $query,
      ':url' => $query,
      ':notes' => $query,
      ];
  }
  $sql = "SELECT
            bt.url,
            bt.image,
            bt.description,
            bt.title,
            bt.favicon,
            bt.notes,
            bt.date,
            bt.bookmark_uuid,
            bookmarks_tags.tag_id,
            total.max as total_bookmarks
          FROM
            (SELECT 
              bookmarks.url,
              bookmarks.image,
              bookmarks.description,
              bookmarks.title,
              bookmarks.favicon,
              bookmarks.notes,
              bookmarks.date,
              bookmarks.bookmark_uuid from bookmarks 
            $sql_where
            $sql_orderby
            LIMIT $limit OFFSET $max) as bt
          LEFT JOIN bookmarks_tags ON bookmarks_tags.bookmark_uuid = bt.bookmark_uuid
          LEFT JOIN (SELECT 
                      count(bookmarks.url) as max
                      from bookmarks 
                    $sql_where
                    ) as total
          $sql_orderby_2
          ";
  //debugme($sql);
  [$db,$result]= askBD($sql,$params);
  $total = 0;
  while ($row = $result->fetchArray()){
    $total = $row['total_bookmarks'];
    //bookmark info
    $bookmarks[$row['bookmark_uuid']]['url'] = $row['url'];
    $bookmarks[$row['bookmark_uuid']]['title'] = $row['title'];
    $bookmarks[$row['bookmark_uuid']]['description'] = $row['description'];
    $bookmarks[$row['bookmark_uuid']]['favicon'] = $row['favicon'];
    $bookmarks[$row['bookmark_uuid']]['notes'] = $row['notes'];
    $bookmarks[$row['bookmark_uuid']]['date'] = $row['date'];
    $bookmarks[$row['bookmark_uuid']]['image'] = $row['image'];
    $bookmarks[$row['bookmark_uuid']]['uuid'] = $row['bookmark_uuid'];
    //tag exists
    if($row['tag_id']){
      $bookmarks[$row['bookmark_uuid']]['tags'][] = $row['tag_id'];
    }
    if(!isset($bookmarks[$row['bookmark_uuid']]['tags'])){ $bookmarks[$row['bookmark_uuid']]['tags'] = []; }
  }
  //has more items to load
  $has_more = $max + count($bookmarks) < $total ? 1 : 0;

  $final = ['bookmarks' => array_values($bookmarks), 'has_more' => $has_more];
  if(REDIS){
    setRedisKey('bookmarks','query_'.$sort_by_date.'_'.$order_desc.'_'.$page.'_'.$query,$final);
  }
  return $final;
}
/**
  * get all bookmarks info
  * @param bool order by [name,date]
  * @param bool sort by [asc,desc]
  * @param int page [0,...]
  * @param arr of ints of tags to search
  * 
  * @return arr [bookmarks=> bookmarks info, has_more => bool]
*/
function get_bookmarks_with_tag($sort_by_date=true,$order_desc=true,$page=0,$tags=[]){
  if(REDIS){
    $bookmarks = getRedisKey('bookmarks','tags_'.$sort_by_date.'_'.$order_desc.'_'.$page.'_'.implode(",",$tags));
    if($bookmarks){ return $bookmarks; }
  }
  //order by
  $sql_orderby = $sort_by_date ? "ORDER BY bookmarks.date " : " ORDER BY bookmarks.title ";
  $sql_orderby_2 = $sort_by_date ? "ORDER BY bt.date " : " ORDER BY bt.title ";
  //sort by
  $sql_orderby .= $order_desc ?  " DESC " : " ASC  ";
  $sql_orderby_2 .= $order_desc ?  " DESC " : "  ASC ";
  //offset
  $max = MAX_ITEMS * intval($page);
  //limit
  $limit = MAX_ITEMS;
  $bookmarks = [];
  $params = [];
  //where
  $sql_where = "  ";
  $total_tags = 0;
  if($tags && is_array($tags)){
    //$query = "%$query%";
    $total_tags = count($tags);
    $sql_where = "WHERE" ;
    foreach ($tags as $key => $tag) {
      if($key == 0){
        $sql_where .= " bookmarks_tags.tag_id = :tag_$key ";
      }
      else {
        $sql_where .= " OR bookmarks_tags.tag_id = :tag_$key";
      }
      $params[":tag_$key"] = $tag;
    }
  }
  $sql = "SELECT
                bt.url,
                bt.image,
                bt.description,
                bt.title,
                bt.favicon,
                bt.notes,
                bt.date,
                bt.bookmark_uuid,
                bookmarks_tags.tag_id,
                total,
                tresults.btotal AS total_bookmarks
            FROM
                (
                SELECT
                    *,
                    COUNT(a.bookmark_uuid) AS total
                FROM
                    (
                    SELECT
                        bookmarks.url,
                        bookmarks.image,
                        bookmarks.description,
                        bookmarks.title,
                        bookmarks.favicon,
                        bookmarks.notes,
                        bookmarks.date,
                        bookmarks.bookmark_uuid,
                        bookmarks_tags.tag_id
                    FROM
                        bookmarks
                    LEFT JOIN bookmarks_tags ON bookmarks_tags.bookmark_uuid = bookmarks.bookmark_uuid
                    $sql_where 
                    $sql_orderby
                    LIMIT $limit OFFSET $max
                ) AS a
            GROUP BY
                a.bookmark_uuid
            HAVING
                COUNT(a.bookmark_uuid) = $total_tags
            ) AS bt
            LEFT JOIN bookmarks_tags ON bookmarks_tags.bookmark_uuid = bt.bookmark_uuid
            LEFT JOIN(
                SELECT
                    COUNT(*) AS btotal
                FROM
                    (
                    SELECT
                        COUNT(bookmarks.bookmark_uuid) AS btotal
                    FROM
                        bookmarks
                    LEFT JOIN bookmarks_tags ON bookmarks_tags.bookmark_uuid = bookmarks.bookmark_uuid
                    $sql_where
                    GROUP BY
                        bookmarks.bookmark_uuid
                    HAVING
                        COUNT(bookmarks.bookmark_uuid) = $total_tags
                ) AS trtotal
            ) AS tresults
            $sql_orderby_2
            ";
  //debugme($sql);
  [$db,$result]= askBD($sql,$params);
  $total = 0;
  while ($row = $result->fetchArray()){
    $total = $row['total_bookmarks'];
    //bookmark info
    $bookmarks[$row['bookmark_uuid']]['url'] = $row['url'];
    $bookmarks[$row['bookmark_uuid']]['title'] = $row['title'];
    $bookmarks[$row['bookmark_uuid']]['description'] = $row['description'];
    $bookmarks[$row['bookmark_uuid']]['favicon'] = $row['favicon'];
    $bookmarks[$row['bookmark_uuid']]['notes'] = $row['notes'];
    $bookmarks[$row['bookmark_uuid']]['date'] = $row['date'];
    $bookmarks[$row['bookmark_uuid']]['image'] = $row['image'];
    $bookmarks[$row['bookmark_uuid']]['uuid'] = $row['bookmark_uuid'];
    //tag exists
    if($row['tag_id']){
      $bookmarks[$row['bookmark_uuid']]['tags'][] = $row['tag_id'];
    }
    if(!isset($bookmarks[$row['bookmark_uuid']]['tags'])){ $bookmarks[$row['bookmark_uuid']]['tags'] = []; }
  }
  //has more items to load
  $has_more = $max + count($bookmarks) < $total ? 1 : 0;
  $final = ['bookmarks' => array_values($bookmarks), 'has_more' => $has_more];
  if(REDIS){
    setRedisKey('bookmarks','tags_'.$sort_by_date.'_'.$order_desc.'_'.$page.'_'.implode(",",$tags),$final);
  }
  return $final;
}
/**
 * 
 * @return arr stats
 * 
 */
function get_bookmarks_tags_stats(){
  $sql = "SELECT * 
            FROM 
            (SELECT count(bookmark_uuid) as t_bookmarks FROM bookmarks) as tb,
            (SELECT count(tags.tag_id) as t_tags FROM tags) as tt
          ";

  [$db,$result]= askBD($sql,[]);
  $stats = ['total_tags' =>0,'total_bookmarks'=>0];
  while ($row = $result->fetchArray()){
    $stats = [
      'total_tags' => $row['t_tags'],
      'total_bookmarks' => $row['t_bookmarks']
    ];
  }
  return $stats;
}
