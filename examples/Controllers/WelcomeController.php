<?php

namespace MiniRouter\Examples\Controllers;

use MiniRouter\Examples\SingletonRouter;

abstract class WelcomeController
{
    static function welcomeView($name1, $name2)
    {
        include "views/welcomeView.php";
    }

    static function welcomeRedirect($name="Morad"){
        // get the global router object to use its registered routes
        $router = SingletonRouter::getRouter();
        // if the third argument is true
        // the router will redirect the browser to the specified route
        $router->route("welcome", ["Redirected", $name]);
    }

    static function welcomeClone($name="Hassan"){
        // get the global router object to use its registered routes
        $router = SingletonRouter::getRouter();
        // route to the route named "welcome" with the following parameters
        $router->route("welcome", [$name, "Clone"], false);
    }
}

