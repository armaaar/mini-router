<?php
// add miniRouter
require_once('../miniRouter.php');
// add any controllers that maybe used
require_once('controllers/simpleControllers.php');
require_once('controllers/welcomeControllers.php');
require_once('controllers/classControllers.php');
require_once('controllers/apiControllers.php');

$router = new miniRouter();

require_once('filters.php');

// The router adds only the domain name at the beginning of any route
// so if you want all your routes inside a subfolder or subdirectory
// group all your routes with the folder name as prefix
$router->group("/examples", function($router){

  // simple route
  $router->get('/', function(){
    echo "This is the homepage. Welcome!";
  });

  // route to a controller defined as a function
  $router->get('/simple', "simpleController");

  // controllers can provide templates in any desired way
  $router->get('/simple-view', "simpleViewController");

  // miniRouter can work on GET, HEAD, POST, PUT, PATCH and DELETE methods
  // Note that HEAD requests won't return any content.
  $router->any('/method', function(){
    echo "The HTTP verp you used to access this page is ".$_SERVER['REQUEST_METHOD'].".";
  });


  // you can pass parameters to controllers through URL using shortcuts
  $router->get('/hello/{:a}/', function($name) {
    echo "Hello, $name!";
  });

  // or you can use regular expressions directly into the URL
  $router->get('/hello-robot/(\d+)', function($robotNumber) {
    echo "Hello robot number $robotNumber!";
  });

  // URL parameters assigned to controller parameters in order
  // Note that regex can be used side by side with shortcuts
  $router->get('/name-match/{:s}/([a-zA-Z0-9+_\-\.]+)', function($name1, $name2) {
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

  // We can assign a name to a route to use later for reverse routing
  $router->get(['/welcome/{:s}/{:s}', "welcome"], "welcomePageController");

  // Route can just call the named function without redirecting to it
  $router->get('/welcome-clone/{:s}?/', "welcomeCloneController");

  // Or it can redirect the request to the named route
  // Note that redirected routed can only be with GET or HEAD requests
  $router->get('/welcome-redirect/{:s}?/', "welcomeRedirectController");


  // a controller can be a method inside an object
  $router->get('/hello-user', [new UserControllers(), 'helloUser']);

  // You can Add a filter that must be true before a router can be accessed
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

    $router->get('/', "getInterface");

    $router->put('/ping-pong', "pingPong");

    $router->get('/boxes', "getBoxes");

    $router->get('/users', "getUsers", "is_admin");

    $router->post('/echo', "echoBack");

  }, ["is_user"]);

});

// The fallback route is the route served if no route was matched yet
// MAKE SURE THAT the fallback route is the last route in your list because
// if it is called before the requested route both the fallback and the requested route will execute their callbacks
// This behaviour is left unchanged to allow several fallbacks in case that
// an app needs to execute some function if a list of routes aren't matched
$router->fallback(function(){
  echo "Page Not Found";
});
