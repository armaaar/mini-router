<?php

function getInterface()
{
  include "views/apiInterface.html";
}

function pingPong()
{
  // Using miniRouter, parameters passed through any method
  // Can be accessed from the global $_REQUEST variable
  if(isset($_REQUEST["move"])) {
    $userMove = strtolower($_REQUEST["move"]);
    if($userMove == 'ping') {
      echo "Pong!";
    } elseif($userMove == 'pong') {
      echo "Ping!";
    } else {
      echo "invalid move. I Win!";
    }
  } else {
    echo "invalid move. I Win!";
  }
}

function getBoxes(){
  echo json_encode(["box of tools", "box of toys", "box of frogs"]);
}

function getUsers(){
  $users = array();

  for ($i=0; $i < 5; $i++) {
    $myUser = new stdClass;

    $myUser->name = "John";
    $myUser->age = 30;
    $myUser->city = "New York";

    $users[] = $myUser;
  }
  echo json_encode($users);
}

function echoBack(){
  // Using miniRouter, data sent with content-type 'application/json' can be accessed from `$_REQUEST` directly.
  var_dump($_REQUEST);
}
