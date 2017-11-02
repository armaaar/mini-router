<?php

class RouterBrain
{
    private $prefixes = [];
    private $filters = [];
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
    private function uri_controller_mapper($uri, $controller, $filters=null)
    {
      // check if there is no uri matcher yet
      if(!$this->uri_matched)
      {
        // check if this is the right uri
        $current_uri = $this->get_uri();
        $uri = $this->add_prefixes_to_uri($uri);
        if($uri == $current_uri)
        {
          // Check if all filters return true
          if($this->filters_pass($filters))
          {
            $this->uri_matched = true;
            $controller();
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

    public function group($prefix, $callback)
    {
      array_push($this->prefixes, $prefix);
      $callback($this);
      array_pop($this->prefixes);
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


    // Methods Functions
    public function get($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'GET')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function head($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'HEAD')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function post($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'POST')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function put($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'PUT')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function patch($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'PATCH')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function delete($uri, $controller, $filters=null)
    {
      if($this->http_method() === 'DELETE')
      {
        $this->uri_controller_mapper($uri, $controller, $filters);
      }
    }

    public function any($uri, $controller, $filters=null)
    {
        $this->uri_controller_mapper($uri, $controller, $filters);
    }

    public function fallback($controller)
    {
      if(!$this->uri_matched)
      {
        $controller();
      }
    }

}
