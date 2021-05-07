<?php
 if(
  defined('FIRSTRUN') 
  && FIRSTRUN 
  && $_SERVER['REQUEST_METHOD'] == 'POST' 
  && isset($_POST['pwd'])
  ){ 
    //debugme("SET ADMIN PASSWORRD");
    //delete current admin if exists
    if(is_logged_in()) { log_out(); }
    delete_admin();
    $result = insert_admin($_POST['pwd']);
    //debugme($result);
    if(isset($result['success'])){
      unlink(ABSPATH . '/firstrun');
      header('Location: '. get_base_url() . '/manage');
      exit;
    }
    //Shouldn't ever be here
    debugme("Admin insertion failed, do the folder has the 775 permissions?");
 } ?>

<div class="withmargins">

<?php

//debugme($_SERVER['REQUEST_METHOD'],$_POST,$_GET);

if(defined('FIRSTRUN') && FIRSTRUN && $_SERVER['REQUEST_METHOD'] == 'GET'){ ?>
<div class="withmargins adminzone">
  <h1>Teikirize - bookmarks</h1>
  <form action="" method="post" class="form">
    <input type="password" name="pwd" id="pwd" placeholder="set admin password">
    <button type="submit" class="btn">Submit</button>
  </form>
</div>
<link rel="stylesheet" href="<?php echo get_base_url(); ?>/assets/css/admin.min.css">

<?php } ?>


</div>
