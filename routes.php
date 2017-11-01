<?php

require_once('router/router.php');
require_once('controllers/homepage.php');

$router = new RouterBrain();

$router->get('/', "homePageController");

$router->get('/laugh', function(){
  echo "hahaha";
});
