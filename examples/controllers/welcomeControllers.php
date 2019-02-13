<?php

function welcomePageController($name1, $name2)
{
  include "views/welcomeView.php";
}

function welcomeRedirectController($name="Morad"){
  global $router;
  // if the third argument is true
  // the router will redirect the browser to the specified route
  $router->route("welcome", ["Redirected", $name]);
}

function welcomeCloneController($name="Hassan"){
  // get the global router object to use its regestered routes
  global $router;
  // route to the route named "welcome" with the following parameters
  $router->route("welcome", [$name, "Clone"], false);
}
