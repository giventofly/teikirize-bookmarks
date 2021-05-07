<?php

?>
<div class="withmargins adminzone">
<?php


//import bookmarks
if(is_logged_in() && isset($_GET['import']) && file_exists( ABSPATH . '/databases/bookmarks.json' )){
  debugme(import_bookmarks());
}
//catch errors
if(isset($response['error']) && $response){
  debugme($response['error']);
}
if(isset($response['success']) && $response){
  debugme($response['success']);
}


//export bookmarks
//TODO

//******* */
if(!is_logged_in()){ 
  //show login form
  ?>
  <form action="" method="post" class="form">
    <input type="password" name="loginpwd" id="loginpwd" placeholder="password">
    <button type="submit" class="btn">Login</button>
  </form>
<?php }
if(is_logged_in()){
  //logout
  ?>
  <a href="<?php echo get_base_url(); ?>/manage?logout" class="logout btn">logout</a>
  <?php
  //show stats
  $server_info = get_server_info();
  ?>

    <p>Php version <span><?php echo $server_info['php version']; ?></span></p>
    <p>Supports REDIS <span><?php if($server_info['REDIS available']){ echo "yes";} else { echo "no"; }; ?></span></p>
    <p>Use REDIS <span><?php if($server_info['use REDIS']){ echo "yes";} else { echo "no"; }; ?></span></p>
    <p>REDIS cache time <span><?php echo $server_info['REDIS CACHE TIME']; ?>s => <?php echo secondsToTime($server_info['REDIS CACHE TIME']); ?></span></p>
    <p>Private mode <span><?php if($server_info['PRIVATE MODE']){ echo "yes";} else { echo "no"; }; ?></span></p>
    <p>Basepath <span><?php echo $server_info['BASEPATH']; ?></span></p>
    <p>Base URL <span><?php echo $server_info['base url']; ?></span></p>
    <p>Max items <span><?php echo $server_info['MAX ITEMS']; ?></span></p>
    <p>Total bookmarks <span><?php echo $server_info['TOTAL BOOKMARKS']; ?></span></p>
    <p>Total tags <span><?php echo $server_info['TOTAL TAGS']; ?></span></p>
    <p class="action">Put the <span>bookmarks.json</span> file in the database folder and click <a href="">here</a> to import them.</p>
    <p>To export your bookmarks to a <span>json</span> file, click <a href="<?php echo get_base_url(); ?>/api/export" target="_blank">here</a></p>
  <?php
    //form change password
  ?>
  <form action="" method="post" class="form">
    <input type="password" name="currentpwd" id="currentpwd" placeholder="current password">
    <input type="password" name="newpwd" id="newpwd" placeholder="new password">
    <button type="submit" class="btn">Change password</button>
  </form>


  <?php
}
?>
</div>


