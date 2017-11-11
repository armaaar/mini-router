<?php

require_once('router/router.php');
require_once('controllers/homepage.php');

$user = true;
$admin = true;

$router = new miniRouter();

$router->filter("is_user", function(){
  global $user;
  if($user)
    return true;
  return false;
});


$router->filter("is_admin", function(){
  global $admin;
  if($admin)
    return true;
  return false;
});

$router->get('/', "homePageController");

$router->group("/router", function($router){

  $router->get(['/', "index"], "homePageController");

  $router->get('/home', "homeCloneController");

  $router->get('/laugh', function($args){
    echo "hahaha ".$args["lala"];
  }, ["is_user", "is_admin"]);

  $router->group('/api', function($router){

    $router->get('/users', function(){
      echo "list of users";
    });
    $router->get('/boxes', function(){
      echo "list of boxes";
    });
  }, ["is_user", "is_admin"]);

});




$router->fallback(function(){
  echo "Page Not Found";
});
