<?php

declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';

use MiniRouter\Examples\controllers\ApiController;
use MiniRouter\Examples\Controllers\UserControllers;
use MiniRouter\Examples\Controllers\WelcomeController;
use MiniRouter\Examples\Filters;
use MiniRouter\Examples\SingletonRouter;
use MiniRouter\MiniRouter;

$router = SingletonRouter::getRouter();
Filters::addFilters();

// The router adds only the domain name at the beginning of any route
// so if you want all your routes inside a subfolder or subdirectory
// group all your routes with the folder name as prefix
$router->group("/mini-router", function(MiniRouter $router){

    // simple route
    $router->get('/', function(){
        echo "This is the homepage. Welcome!";
    });

    // route to a controller defined as a function
    $router->get('/simple', "MiniRouter\Examples\Controllers\simpleController");

    // controllers can provide templates in any desired way
    $router->get('/simple-view', "MiniRouter\Examples\Controllers\simpleViewController");

    // mini-router can work on GET, HEAD, POST, PUT, PATCH and DELETE methods
    // Note that HEAD requests won't return any content.
    $router->any('/method', function(){
        echo "The HTTP method you used to access this page is ".$_SERVER['REQUEST_METHOD'].".";
    });


    // you can pass parameters to controllers through URL using shortcuts
    $router->get('/hello/{:a}/', function(string $name) {
        echo "Hello, $name!";
    });

    // or you can use regular expressions directly into the URL
    $router->get('/hello-robot/(\d+)', function(string $robotNumber) {
        echo "Hello robot number $robotNumber!";
    });

  // URL parameters assigned to controller parameters in order
  // Note that regex can be used side by side with shortcuts
    $router->get('/name-match/{:s}/([a-zA-Z0-9+_\-\.]+)', function(string $name1, string $name2) {
        echo "Hello, $name1!<br>";
        echo "Hello, $name2!<br>";
        if($name1 == $name2) {
            echo "Your names are matched!";
        } else {
            echo "Your names are not matched!";
        }
    });

    // Parameters could be optional but you need to define default values for it's corresponding variables
    $router->get('/hello-anon/{:a}?/', function($name = "anonymous") {
        echo "Hello, $name!";
    });

    // a controller can be a method inside an object
    $router->get('/hello-user', [new UserControllers(), 'helloUser']);

    // a controller can be a static method of a class
    $router->get('/static-hello-user/{:s}?', [UserControllers::class, 'staticHelloUser']);

    // You can Add a filter that must be true before a router can be accessed
    // try changing return values of filters inside `filter.php` to access this page
    $router->get('/laugh', function(){
        echo "Admins are so funny. HA HA HA!";
    }, "is_admin");

    $user = new UserControllers("Ahmed");
    // filters works only before the controller callback is executed
    $router->get('/user-settings', [$user, 'settingsView'], "is_user");
    // You can apply more than one filter to a route
    $router->get('/user-life', [$user, 'controlUserLife'], ["is_user", "is_admin"]);


    // groups apply prefixes to URLS and can be used to filter all the routes inside it
    $router->group('/api', function($router){

        $router->get('/', [ApiController::class, "getInterface"]);

        $router->put('/ping-pong', [ApiController::class, "pingPong"]);

        $router->get('/boxes', [ApiController::class, "getBoxes"]);

        $router->get('/users', [ApiController::class, "getUsers"], "is_admin");

        $router->post('/echo', [ApiController::class, "echoBack"]);

    }, ["is_user"]);

    // We can assign a name to a route to use later for reverse routing
    $router->get(['/welcome/{:s}/{:s}', "welcome"], [WelcomeController::class, "welcomeView"]);

    // We can redirect the request to a named route
    // Note that redirected routed can only be with GET or HEAD requests
    $router->get('/welcome-redirect/{:s}?/', [WelcomeController::class, "welcomeRedirect"]);

    // Route can just call the named function without redirecting to it
    // This feature can be used as a middleware if needed
    $router->get('/welcome-clone/{:s}?/', [WelcomeController::class, "welcomeClone"]);
});

// The fallback route is the route served if no route was matched yet
$router->fallback(function(){
    http_response_code(404);
    echo "Page Not Found";
});

// No routes would be matched without starting the routing function
// This allows the router to register all routes first before running any controller
// this is useful for reverse routing
$router->start_routing();
