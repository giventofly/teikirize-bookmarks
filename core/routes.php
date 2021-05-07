<?php

/**
 * 
 * check the current url and do the appropriate action
 * original from https://github.com/steampixel/simplePHPRouter
 * 
 * @param arr routes
 * 
 */
function routes($routes=[]){
  // The basepath never needs a trailing slash
  // Because the trailing slash will be added using the route expressions
  $basepath = rtrim(BASEPATH, '/');
  //$basepath = $routes[0]['expression'];
  //$basepath = BASEPATH;
  // Parse current URL
  $parsed_url = parse_url($_SERVER['REQUEST_URI']);
  //last trail matters?
  $trailing_slash_matters = false;
  //case sensitive?
  $case_matters = false;

  $path = '/';
  // If there is a path available
  if (isset($parsed_url['path'])) {
    // If the trailing slash matters
    if ($trailing_slash_matters) {
      $path = $parsed_url['path'];
    } else {
      // If the path is not equal to the base path (including a trailing slash)
      if($basepath.'/'!=$parsed_url['path']) {
        // Cut the trailing slash away because it does not matters
        $path = rtrim($parsed_url['path'], '/');
      } else {
        $path = $parsed_url['path'];
      }
    }
  }
  $path = urldecode($path);

  //debugme("path",$path,"basepath",$basepath,"parsed url",$parsed_url);

  // Get current request method
  $method = $_SERVER['REQUEST_METHOD'];
  $path_match_found = false;
  $route_match_found = false;
  $multimatch = false;

  function methodNotAllowed($path,$method){ echo "method $method not allowed for $path"; };
  function pathNotFound($path){ echo "path $path not found"; };

  //debugme("method",$method);

  foreach ($routes['routes'] as $route) {
    //debugme("route",$route);
    // If the method matches check the path
    //debugme($route['expression']);
    // Add basepath to matching string
    if ($basepath != '' && $basepath != '/') {
      $route['expression'] = '('.$basepath.')'.$route['expression'];
    }
    //debugme($route['expression']);
    // Add 'find string start' automatically
    $route['expression'] = '^'.$route['expression'];
    // Add 'find string end' automatically
    $route['expression'] = $route['expression'].'$';
    // Check path match
    if (preg_match('#'.$route['expression'].'#'.($case_matters ? '' : 'i').'u', $path, $matches)) {
      $path_match_found = true;
      // Cast allowed method to array if it's not one already, then run through all methods
      foreach ((array)$route['method'] as $allowedMethod) {
          // Check method match
        if (strtolower($method) == strtolower($allowedMethod)) {
          array_shift($matches); // Always remove first element. This contains the whole string

          if ($basepath != '' && $basepath != '/') {
            array_shift($matches); // Remove basepath
          }
          if($return_value = call_user_func_array($route['function'], $matches)) {
            echo $return_value;
          }
          $route_match_found = true;
          // Do not check other routes
          break;
        }
      }
    }
    // Break the loop if the first found route is a match
    if($route_match_found&&!$multimatch) {
      break;
    }

  }
  //debugme("route match found",$route_match_found ? 1 : 0,"path match found",$path_match_found ? 1 : 0);
  // No matching route was found
  if (!$route_match_found) {
    // But a matching path exists
    if ($path_match_found) {
        call_user_func_array(isset($routes['methodNotAllowed']) ? $routes['methodNotAllowed'] : 'methodNotAllowed', [$path,$method]);
    } else {
        call_user_func_array(isset($routes['pathNotFound']) ? $routes['pathNotFound'] : 'pathNotFound', [$path]);
    }
  }
  return $route_match_found;
}
