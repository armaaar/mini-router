<?php

namespace MiniRouter\Examples\Controllers;

use MiniRouter\Examples\SingletonRouter;

abstract class WelcomeController
{
    static function welcomeView(string $name1, string $name2): void
    {
        include "views/welcomeView.php";
    }

    static function welcomeRedirect(string $name="Morad"): void
    {
        // get the global router object to use its registered routes
        $router = SingletonRouter::getRouter();
        // if the third argument is true
        // the router will redirect the browser to the specified route
        $router->route("welcome", ["Redirected", $name]);
    }

    static function welcomeClone(string $name="Hassan"): void
    {
        // get the global router object to use its registered routes
        $router = SingletonRouter::getRouter();
        // route to the route named "welcome" with the following parameters
        $router->route("welcome", [$name, "Clone"], false);
    }
}

