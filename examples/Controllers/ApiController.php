<?php

namespace MiniRouter\Examples\controllers;
abstract class ApiController
{

    static function getInterface()
    {
        include "views/apiInterface.html";
    }

    static function pingPong()
    {
      // Using mini-router, parameters passed through any method
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

    static function getBoxes(){
        echo json_encode(["box of tools", "box of toys", "box of frogs"]);
    }

    static function getUsers(){
        $users = array();

        for ($i=0; $i < 5; $i++) {
            $myUser = new \stdClass;

            $myUser->name = "John";
            $myUser->age = 30;
            $myUser->city = "New York";

            $users[] = $myUser;
        }
        echo json_encode($users);
    }

    static function echoBack(){
        // Using mini-router, data sent with content-type 'application/json' can be accessed from `$_REQUEST` directly.
        var_dump($_REQUEST);
    }
}
