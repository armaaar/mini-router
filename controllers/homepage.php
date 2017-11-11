<?php

function homePageController($name, $name2)
{
  echo $name."  $name2";
  include "views/homepage.html";
}

function homeCloneController($name="hassan"){
  global $router;
  $router->route("index", [$name, "soso"], true);
}
