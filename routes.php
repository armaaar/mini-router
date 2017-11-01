<?php

require_once('router/router.php');
require_once('controllers/homepage.php');

$router = new RouterBrain();

$router->get('/', "homePageController");

$router->get('/laugh', function(){
  echo "hahaha";
});

$router->group('/api', function($router){

  $router->get('/users', function(){
    echo "list of users";
  });
  $router->get('/boxes', function(){
    echo "list of boxes";
  });
});
