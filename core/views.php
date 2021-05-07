<?php

/**
 * 
 * loads header
 * @param string page title
 * @param array css files to load
 * @param array javascript files to load
 * @param string default page name
 * 
 */
function get_header($title='',$css=[],$js=[],$default='Teikizire Bookmarks'){
  require(ABSPATH . '/views/header.php');
}
/**
 * 
 * loads footer
 * @param array css files to load
 * @param array js files to load
 * 
 */
function get_footer($css=[],$js=[]){
  require(ABSPATH . '/views/footer.php');
}
/**
 * 
 * loads admin template
 * 
 */
function load_admin_template(){
  //logout
  if(isset($_GET['logout'])){
    log_out();
    header("Location: " . get_base_url() . "/manage");
  }
  //login
  if(!is_logged_in() && isset($_POST['loginpwd'])){ 
    $response = log_in($_POST['loginpwd']);
  }
  //change password
  if(is_logged_in() && isset($_POST['currentpwd']) && isset($_POST['newpwd'])){ 
    $response = change_password($_POST['currentpwd'],$_POST['newpwd']);
  }
  get_header('Administration',['admin.min.css']);
  require(ABSPATH . '/views/manage.php');
  get_footer();
}
/**
 * 
 * first boot or force password change
 * 
 */
function load_first_run(){
  //get_header('Set admin password');
  require(ABSPATH . '/views/firstrun.php');
  get_footer();
}
/**
 * 
 * root template
 * 
 */
function load_root_template(){
  $js = ['helpers.js','app.js'];
  $css = [];
  $title = '';
  get_header($title,$css,$js);
  require(ABSPATH . '/views/base.php');
  get_footer();
}
