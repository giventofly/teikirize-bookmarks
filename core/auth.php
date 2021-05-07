<?php /* auth related functions */ 

/**
 * 
 * @return im-delight object
 * 
 */
function askAuth(){
  $db = new \PDO('sqlite:'.ABSPATH . '/databases/auth.sqlite') or die('Unable to open database');
  $auth = new \Delight\Auth\Auth($db);
  return $auth;
}
/**
 * 
 * @param string password
 * @return bool
 * 
 */
function insert_admin($password){
  global $auth;
  $response = [];
	try {
    $userId = $auth->registerWithUniqueUsername('admin@localhost.dev',$password,'admin');
  }
  catch (\Delight\Auth\InvalidEmailException $e) {
        $response['error'] = 'Invalid email address';
  }
  catch (\Delight\Auth\InvalidPasswordException $e) {
        $response['error'] = 'Invalid password';
  }
  catch (\Delight\Auth\UserAlreadyExistsException $e) {
        $response['error'] = 'User already exists';
  }
  catch (\Delight\Auth\TooManyRequestsException $e) {
        $response['error'] = 'Too many requests';
  }
  catch (\Delight\Auth\DuplicateUsernameException $e) {
      $response['error'] = 'Username already exists';
  }
  if(!isset($response['error'])) {
      // keep logged in for one year
      $rememberDuration = (int) (60 * 60 * 24 * 365.25);
      // do not keep logged in after session ends
      $auth->loginWithUsername('admin',$password,$rememberDuration);
      $response['success'] = 'All right';
  }
  //debugme("inserted admin");
  return $response;
}
/**
 * 
 * deletes admin username from database
 * 
 */
function delete_admin(){
  global $auth;
  try {
    $auth->admin()->deleteUserByUsername('admin');
    $response['success'] = "Yeah!";
  }
  catch (\Delight\Auth\UnknownUsernameException $e) {
    return false;  
    $response['error'] = 'Unknown username';
  }
  catch (\Delight\Auth\AmbiguousUsernameException $e) {
    return false;  
    $response['error'] = 'Ambiguous username';
  }
  return $response;
}
/**
 * 
 * verifies current user is logged in (there is only one user registered at all times)
 */
function is_logged_in(){
  global $auth;
  return $auth->isLoggedIn();
}
/**
 * 
 * @param string
 * 
 * @return true if logged successfully false otherwise
 * 
 */
function log_in($password){
  global $auth;
  $rememberDuration = (int) (60 * 60 * 24 * 365.25);
  $response = [];
  try {
    $auth->loginWithUsername('admin',$password,$rememberDuration);
    $response['success'] = "logged in";
  }
  catch (\Delight\Auth\InvalidEmailException $e) {
      $response['error'] = ['Wrong email address'];
  }
  catch (\Delight\Auth\InvalidPasswordException $e) {
      $response['error'] = ['Wrong password'];
  }
  catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $response['error'] = ['Email not verified'];
  }
  catch (\Delight\Auth\TooManyRequestsException $e) {
      $response['error'] = ['Too many requests'];
  }
  return $response;
}
/**
 * 
 * @param string current_password
 * @param string new_password
 *
 * @return true on successfully change, false otherwise
 * 
 */
function change_password($current_password,$new_password){
  global $auth;
  try {
    $auth->changePassword($current_password, $new_password);
    $response['success'] =  'Password has been changed';
  }
  catch (\Delight\Auth\NotLoggedInException $e) {
      $response['error'] = 'Not logged in';
  }
  catch (\Delight\Auth\InvalidPasswordException $e) {
      $response['error'] = 'Invalid password(s)';
  }
  catch (\Delight\Auth\TooManyRequestsException $e) {
      $response['error'] = 'Too many requests';
  }
  return $response;
}
/** 
 * 
 * log out
 * 
 */
function log_out(){
  global $auth;
  $auth->logOut();
}