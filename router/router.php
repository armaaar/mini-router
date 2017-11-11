<?php

class miniRouter
{
    private $prefixes = [];
    private $filters = [];
    private $routes = [];
    private $uri_matched = false;

    // essential functions
    public function get_uri()
    {
      $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
      // get rid of double slashes
      return $this->prepare_uri($uri);
    }

    public function http_method()
    {
      return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    private function prepare_uri($uri)
    {
      // make sure there are slashes before and after uri
      $uri = '/'.$uri.'/';
      // get rid of extra slashes
      return preg_replace('/(\/+)/','/',$uri);
    }

    // map URI to Controller
    private function uri_controller_mapper($uri, $controller, $filters=null, $skip_uri_match = false)
    {
      // check if there is no uri matcher yet
      if(!$this->uri_matched)
      {
        // check if this is the right uri
        $current_uri = $this->get_uri();
        $uri = $this->add_prefixes_to_uri($uri);
        if($uri == $current_uri || $skip_uri_match)
        {
          // Check if all filters return true
          if($this->filters_pass($filters))
          {
            $this->uri_matched = true;
            // call the controller passing array of request parameters
            $controller($this->request_parameters($this->http_method()));
          }
        }
      }
    }

    // Prefixes and groups
    private function add_prefixes_to_uri($uri)
    {
      $uri = join("",$this->prefixes).'/'.$uri;
      return $this->prepare_uri($uri);
    }

    public function group($prefix, $callback, $filters=null)
    {
      if($this->filters_pass($filters))
      {
        array_push($this->prefixes, $prefix);
        $callback($this);
        array_pop($this->prefixes);
      }
    }

    // filters
    public function filter($name, $filter)
    {
      $this->filters[$name] = $filter;
    }
    private function filters_pass($filters)
    {
      if($filters)
      {
        if(!is_array($filters))
        {
          $filters = [$filters];
        }
        foreach ($filters as $filter) {
          if(array_key_exists($filter,$this->filters))
          {
            $pass = $this->filters[$filter]();
            if(!$pass)
            {
              return false;
            }
          } else {
            trigger_error("Filter '$filter' is not defined", E_USER_NOTICE);
            return false;
          }

        }
      }
      return true;
    }

    // Register Routes
    private function register_route($uri, $name, $controller, $filters, $method)
    {
      // Check if route name has a record
      if(!array_key_exists($name, $this->routes))
      {
        $this->routes[$name] = ["uri" => $uri, "controller" => $controller, "prefixes" => $this->prefixes,
                              "filters" => $filters, "method" => $method];
      } else {
        trigger_error("Route '$name' defined more than once", E_USER_NOTICE);
        return false;
      }

    }

    public function route($name, $redirect = false)
    {
      //$this->routes[$name] = ["uri" => $uri, "controller" => $controller, "prefixes" => $this->prefixes, "filters" => $filters, "method" => $method];
      if(array_key_exists($name, $this->routes))
      {
        $old_prefixes = $this->prefixes;
        $this->prefixes = $this->routes[$name]["prefixes"];
        if($redirect)
        {
          if($this->routes[$name]["method"] === "GET" || $this->routes[$name]["method"] === "HEAD")
          {
            $uri = $this->add_prefixes_to_uri($this->routes[$name]["uri"]);
            header("Location: ".$uri);
            return true;
          } else {
            trigger_error("Can't redirect to route '$name' with ".$this->routes[$name]["method"]." method", E_USER_NOTICE);
            $this->prefixes = $old_prefixes;
            return false;
          }

        } else { // IF NOT REDIRECT
          if($this->routes[$name]["method"] === $this->http_method())
          {
            $this->uri_matched = false;
            $this->uri_controller_mapper($this->routes[$name]["uri"], $this->routes[$name]["controller"], $this->routes[$name]["filters"], true);
          } else {
            trigger_error("Can't redirect to route '$name' because it has different method from requested", E_USER_NOTICE);
            $this->prefixes = $old_prefixes;
            return false;
          }
        }
        $this->prefixes = $old_prefixes;
      } else {
        trigger_error("Route '$name' is not defined", E_USER_NOTICE);
        return false;
      }
    }

    // Request parameters
    private function request_parameters($request_method)
    {
      $params = [];
      if ($request_method == "GET" || $request_method == "HEAD") {
          $params = $_GET;
      } else if ($request_method == "POST") {
          $params = $_POST;
      } else if ($request_method == "PUT" || $request_method == "DELETE" || $request_method == "PATCH") {
        parse_str(file_get_contents('php://input'), $params);
      } else
      return $params;
    }

    // Methods Functions
    public function get($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'GET');
      }

      if($this->http_method() === 'GET')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function head($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'HEAD');
      }

      if($this->http_method() === 'HEAD')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function post($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'POST');
      }

      if($this->http_method() === 'POST')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function put($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'PUT');
      }

      if($this->http_method() === 'PUT')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function patch($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'PATCH');
      }

      if($this->http_method() === 'PATCH')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function delete($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'DELETE');
      }

      if($this->http_method() === 'DELETE')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function any($uri, $controller, $filters=null)
    {
      // Check if this route has name
      if(is_array($uri))
      {
        $name = $uri[1];
        $uri = $uri[0];
        $this->register_route($uri, $name, $controller, $filters, 'ANY');
      }

        $this->uri_controller_mapper($uri, $controller, $filters);
    }

    public function fallback($controller)
    {
      if(!$this->uri_matched)
      {
        // call the controller passing array of request parameters
        $controller($this->request_parameters($this->http_method()));
      }
    }

}
