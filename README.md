# miniRouter - Fast PHP regex based router
miniRouter is a fast PHP router based on regular expressions inspired from [PHRoute](https://github.com/mrjgreen/phroute).
It is build essentially to support RESTful APIs with simple, yet powerful, high speed interface.

## Installation
Just include the `miniRouter.php` file in your index page and start using it.

### Friendly URL

In order to handle all requests using the router, you need to redirect all requests to the page you would like to define your routes in. Create a simple .htaccess file on your root directory if you're using Apache with mod_rewrite enabled.

```apache
Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_URI} !\..+$
RewriteRule .* index.php [QSA,L]

```
Check the examples for implementation.

## Usage
### Defining routes

miniRouter supports GET, HEAD, POST, PUT, PATCH and DELETE methods.

```PHP
require_once('miniRouter.php');

$router = new miniRouter();

$router->get($route, $handler);     # match only get requests
$router->post($route, $handler);    # match only post requests
$router->put($route, $handler);     # match only put requests
$router->delete($route, $handler);  # match only delete requests
$router->patch($route, $handler);   # match only patch requests
$router->head($route, $handler);    # match only head requests
$router->any($route, $handler);     # match any request method

$router->start_routing();
```
These methods are defined by the HTTP method the route must match, and accept the route pattern and a callable handler, which can be a closure, function name or ['ClassName', 'method'] pair.

Note that the router doesn't by default echo the returned value from the handler, so if you want to send something back to the client you need to `echo` it, not to `return` it.

### Regex Shortcuts

URLs can use regular expressions directly into the URL or use one of these shortcuts

```
{:i}  => ([0-9]+)              # numbers only
{:a}  => ([0-9A-Za-z]+)        # alphanumeric
{:h}  => ([0-9A-Fa-f]+)        # hex
{:s}  => ([a-zA-Z0-9+_\-\.]+)  # alphanumeric and + _ - . characters

```
Here are some examples of using regex and shortcuts in routes

```PHP

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
  $router->get('/hello-two/{:s}/([a-zA-Z0-9+_\-\.]+)', function($name1, $name2) {
    echo "Hello, $name1 and $name2!<br>";
  });

  // Parameters could be optional but you need to define default values for it's corresponding variables
  $router->get('/hello-anon/{:a}?/', function($name = "anonymous") {
    echo "Hello, $name!";
  });
```

### Named Routes for Reverse Routing

Pass in an array as the first argument, where the first item is your route and the second item is a name to reference it later.

```PHP
$router->get(['/user/{:a}', 'username'], function($name){
    echo 'Hello ' . $name;
});

$router->get(['/page/{:s}/{:i}', 'page'], function($slug, $id){
    echo 'You must be authenticated to see this page: ' . $id;
});

// Use the route name and pass any route parameters to redirect the browser to existing route path
// If you change your route path above, you won't need to go through your code updating any links/references to that route
$router->route('username', 'joe');
// this will redirect the browser to the path '/user/joe'

// if you passed a false value to the third argument, the browser will call the handler of the specified route without redirecting
$router->route('page', ['intro', 234], false);
// this will call the handler of the path '/page/intro/234'
```

### Groups
Groups apply prefixes to URLS.

```PHP

$router->group('/admin', function($router){

    $router->get('pages', function(){
        echo 'page management';
    });

    $router->get('products', function(){
        echo 'product management';
    });

    $router->get('orders', function(){
        echo 'order management';
    });
});
```

Groups can also define parameters in URL, parameters defined in a group is also passed to all group routes by default.

```PHP

$router->group('/api/v{:i}', function($router, $api_version){

    $router->get('users', function($version){
        echo $version;
    });

    if ($api_version >= 2) {
        $router->get('order/{:i}', function($api_version, $order_id){
            echo $api_version;
        });
    }
});
```

It's good to notice that the router adds only the domain name at the beginning of any route. So if all your routes are inside a subfolder or subdirectory, group all your routes with the folder name as prefix.

### Filters

You can Add a filter that must be true before a route can be accessed.

```PHP

$router->filter('isLoggedIn', function(){
    if($_SESSION['logged_in']) {
      return true
    } else {
      return false
    }
});

$router->get('/user/{:a}', function($name){
    echo 'Hello ' . $name;
}, "isLoggedIn");
```
If the filter returned any value that if casted to boolean will be `false`, it will prevent the route handler from being dispatched. You can check `false` values from [PHP booleans documentation](https://secure.php.net/manual/en/language.types.boolean.php#language.types.boolean.casting)

### Filter Groups

Wrap multiple routes in a route group to apply a filter to every route defined within. You can nest groups if required.

```php

$router->filter('auth', function(){
    if(!isset($_SESSION['user']))
    {
        header('Location: /login');
        return false;
    }
    return true;
});

$router->group('/', function($router){

    $router->get('/user/{:a}', function($name){
        echo 'Hello ' . $name;
    });

    $router->get('/page/{:i}', function($id){
        return 'You must be authenticated to see this page: ' . $id;
    });

}, "auth");
```

Check examples for more detailed practical use examples for miniRouter, especially for RESTful APIs.

## License
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
