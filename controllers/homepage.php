<?php

function homePageController()
{
  include "views/homepage.html";
}

function homeCloneController(){
  global $router;
  $router->route("index");
}
