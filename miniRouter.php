<?php

class miniRouter
{
    private $prefixes = [];
    private $filters = [];
    private $routes = [];
    private $uri_matched = false;
    private $inner_route_flag = false;
    private $matched_route_controller = null;
    private $matched_route_parameters = [];
    private $regexShortcuts = array(
        '{:i}'  => '([0-9]+)',
      	'{:a}'  => '([0-9A-Za-z]+)',
      	'{:h}'  => '([0-9A-Fa-f]+)',
        '{:s}'  => '([a-zA-Z0-9+_\-\.]+)'
    );

    // essential functions
    // Get current page URI
    public function get_uri() {
        // get the URI without parameters
        $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        // get rid of double slashes
        return $this->prepare_uri($uri);
    }
    // get current http method
    public function http_method() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    // get rid of double slashes in URI if any exists
    private function prepare_uri($uri) {
        // make sure there are slashes before and after uri
        $uri = '/'.$uri.'/';
        // get rid of extra slashes
        return preg_replace('/(\/+)/','/',$uri);
    }

    // map URI to Controller
    private function matched_route_selector($uri, $controller, $filters=null, $route_args = []) {
        /*
        * this function stores the controller that matches the current URI
        * so it can be called when the router starts
        * or it also calls the controller of the route function
        * if called inside the controller of another route
        */

        // only execute if no uri matched yet or this is an inner route
        if ($this->uri_matched && !$this->inner_route_flag) {
            return false;
        }

        // Make sure that the route URL matches the current URI, and get URI parameters
        // if this is an inner route, no check is needed, the URI parameters are supplied
        if ($this->inner_route_flag) {
            if(!is_array($route_args)) {
                $route_args = [$route_args];
            }
            $parameters = $route_args;
        } else {
            // Match route URL with the current URI and get its parameters
            $parameters = $this->uri_match($uri);
            // return false if the route URI didn't match the current URI
            if ($parameters === false) {
                return false;
            }
        }

        // return if route filters didn't pass
        if (!$this->filters_pass($filters)) {
            return false;
        }

        if ($this->inner_route_flag) {
            // if this is an inner routing, call the requested controller
            $this->inner_route_flag = false;
            call_user_func_array($controller, $parameters);
        } else {
            // store the controller that matches the current URI
            $this->uri_matched = true;
            $this->matched_route_controller = $controller;
            $this->matched_route_parameters = $parameters;
        }
    }

    private function uri_match($uri, $group_match = false) {
        /*
        * Knows if the current URI is the same as the supplied URI
        * If true, returns the route parameters if any
        */
        // var to store URI parameters
        $matches = [];
        // Get Current URI
        $current_uri = $this->get_uri();
        // Add prefixes to the route URI
        $uri = $this->add_prefixes_to_uri($uri);
        // Replace regex shortcuts with actual regexs
        $uri = strtr($uri, $this->regexShortcuts);
        // Escape back slashes in URI
        $uri = preg_replace('/(\/+)/','\/',$uri);
        // return false if the URIs don't match
        $regex = $group_match ? "/^".$uri."?/" : "/^".$uri."?$/";
        if (!preg_match($regex, $current_uri, $matches)) {
            return false;
        }
        // remove the whol URI from the matched groups and leave the groups only
        array_shift($matches);
        // return the parameters
        return $matches;
    }

    // Prefixes and groups
    // Add groups prefixes to the supplied URI
    private function add_prefixes_to_uri($uri) {
        // join existing prefixes with the supplied URI
        $uri = join("",$this->prefixes).'/'.$uri;
        // get rid of double slashes in URI if any exists
        return $this->prepare_uri($uri);
    }

    // register a group of routes
    public function group($prefix, $callback, $filters=null) {
        // register group if only all filter passed
        if ($this->filters_pass($filters)) {
            // add group prefix to the current prefixes list before registering routes
            array_push($this->prefixes, $prefix);
            // pass matched parameters to callback if any
            $parameters = $this->uri_match('', true);
            // return false if the route URI didn't match the current URI
            if ($parameters !== false) {
                // add router instance to parameters
                array_unshift($parameters, $this);
                // call the callback containing routes which needed to be registered in the current group using its prefix
                call_user_func_array($callback, $parameters);
            }
            // remove the added group prefix so it doesn't affect other coming routes outside the group
            array_pop($this->prefixes);
        }
    }

    // filters
    // registers a filter callback in the list of filters
    public function filter($name, $filter) {
        $this->filters[$name] = $filter;
    }
    // check if a list of filters passes
    private function filters_pass($filters) {
        // make sure filters' names are suplied to the function
        if ($filters) {
            // make sure filters are array to generalize the code
            if (!is_array($filters)) {
                $filters = [$filters];
            }
            // iterate over each filter name
            foreach ($filters as $filter) {
                // make sure that the filter is defined in the filters list
                if (!array_key_exists($filter, $this->filters)) {
                    // trigger a warning if the filter is not defined
                    trigger_error("Filter '$filter' is not defined", E_USER_WARNING);
                    // filter fails if it doesn't exist
                    return false;
                }
                // executes the filter
                $pass = $this->filters[$filter]();
                // exit if filter fails, otherwise iterate to the next filter
                if (!$pass) {
                    return false;
                }
            }
         }
        // if no filters are supplied or all filters passed, return true
        return true;
    }

    // Register Routes, maps route name with its URI, controller, filter and method
    private function register_route($uri, $name, $controller, $filters, $method) {
        // Check if route name has a record
        if (!array_key_exists($name, $this->routes)) {
            $this->routes[$name] = [
                "uri" => $uri,
                "controller" => $controller,
                "prefixes" => $this->prefixes,
                "filters" => $filters,
                "method" => $method
            ];
        } else {
            trigger_error("Route '$name' defined more than once", E_USER_WARNING);
            return false;
        }
    }

    // Redirects to another route or call another route's controller by route name
    public function route($name, $args = [], $redirect = true) {
        // only route to registered named routes
        if (!array_key_exists($name, $this->routes)) {
            trigger_error("Route '$name' is not defined", E_USER_WARNING);
            return false;
        }
        // store ord prefixes temporarly and insert requested route prefixes instead
        $old_prefixes = $this->prefixes;
        $this->prefixes = $this->routes[$name]["prefixes"];

        if (!is_array($args)) {
            $args = [$args];
        }

        if ($redirect) {
            // If this is a redirect request
            // Only redirect GET and HEAD requests
            if (
                $this->routes[$name]["method"] !== "GET"
                && $this->routes[$name]["method"] !== "HEAD"
                && $this->routes[$name]["method"] !== "ANY"
            ) {
                trigger_error("Can't redirect to route '$name' with ".$this->routes[$name]["method"]." method", E_USER_WARNING);
                $this->prefixes = $old_prefixes;
                return false;
            }

            // Add prefixes to the route URI
            $uri = $this->add_prefixes_to_uri($this->routes[$name]["uri"]);

            // inster passed arguments to route URI before redirecting
            if (preg_match_all('/({:\w}|\([^\)]+\)*)\??/', $uri, $patterns)) {
                $patterns = $patterns[0];
                foreach ($patterns as $index => $pattern) {
                    $pos = strpos($uri, $pattern);
                    if(!array_key_exists($index, $args)) {
                        $args[$index] = '';
                    }
                    $uri = substr_replace($uri, $args[$index], $pos, strlen($pattern));
                }
                $uri = str_replace($patterns, $args, $uri);
            }
            // redirect to route URI
            $uri = $this->prepare_uri($uri);
            header("Location: ".$uri);
            return true;

        } else { // IF this is an innner route request (not a redirect)
            // route only for the same http method
            if(!$this->routes[$name]["method"] === $this->http_method()  && $this->routes[$name]["method"] !== "ANY") {
            trigger_error("Can't redirect to route '$name' because it has different method from requested", E_USER_WARNING);
            $this->prefixes = $old_prefixes;
            return false;
            }
            // call matched route selector with the inner route flash
            $this->inner_route_flag = true;
            $this->matched_route_selector($this->routes[$name]["uri"], $this->routes[$name]["controller"], $this->routes[$name]["filters"], $args);
        }
        // return old prefixes
        $this->prefixes = $old_prefixes;
    }

    // Add request parameters to $_REQUEST whatever the request type is
    private function set_method_parameters() {
        $params = []; // var to save parameters in
        // check if request is json
        if (
            isset($_SERVER['CONTENT_TYPE'])
            && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        ) {
            // Json decode request parameters
            $params = json_decode(file_get_contents('php://input'), true);
            // add parameters to $_REQUEST
            $_REQUEST = array_merge($_REQUEST, $params);
        } else {
            // get request parameters
            $input = file_get_contents('php://input');
            if ($input) {
                // parses encoded_string containing parameters into array
                parse_str($input, $params);
                // add parameters to $_REQUEST
                $_REQUEST = array_merge($_REQUEST, $params);
            }
        }
    }

    // Methods Functions
    public function get($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'GET');
        }

        if ($this->http_method() === 'GET') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function head($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'HEAD');
        }

        if ($this->http_method() === 'HEAD') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function post($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'POST');
        }

        if ($this->http_method() === 'POST') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function put($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'PUT');
        }

        if ($this->http_method() === 'PUT') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function patch($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'PATCH');
        }

        if ($this->http_method() === 'PATCH') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function delete($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'DELETE');
        }

        if ($this->http_method() === 'DELETE') {
            $this->matched_route_selector($uri, $controller, $filters);
        }
    }

    public function any($uri, $controller, $filters=null) {
        // Check if this route has a name
        if (is_array($uri)) {
            $name = $uri[1];
            $uri = $uri[0];
            $this->register_route($uri, $name, $controller, $filters, 'ANY');
        }
        $this->matched_route_selector($uri, $controller, $filters);
    }

    // Defines the fallback controller if no route was matched
    public function fallback($controller) {
        if (!$this->uri_matched) {
            $this->matched_route_controller = $controller;
            $this->matched_route_parameters = [];
        }
    }

    public function start_routing() {
        // Add request parameters to $_REQUEST
        $this->set_method_parameters();
        // call matched function if any
        if (is_null($this->matched_route_controller)) {
            trigger_error("There is no route matched by the router, and there was no fallback defined.", E_USER_NOTICE);
            return false;
        }
        call_user_func_array($this->matched_route_controller, $this->matched_route_parameters);
        return true;
    }
}
