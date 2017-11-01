<?php

class RouterBrain
{
    private $prefixes = [];

    // essential functions
    public function get_uri()
    {
      return explode('?', $_SERVER['REQUEST_URI'], 2)[0];
    }

    public function http_method()
    {
      return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    // map URI to Controller
    private function uri_controller_mapper($uri, $controller)
    {
      $current_uri = $this->get_uri();
      $uri = $this->add_prefixes_to_uri($uri);
      if($uri == $current_uri || $uri == $current_uri.'/')
      {
        $controller();
      }
    }

    // Prefixes and groups
    private function add_prefixes_to_uri($uri)
    {
      return join("",$this->prefixes).$uri;
    }

    public function group($prefix, $callback)
    {
      array_push($this->prefixes, $prefix);
      $callback($this);
      array_pop($this->prefixes);
    }

    // Methods Functions
    public function get($uri, $controller)
    {
      if($this->http_method() === 'GET')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }

    public function head($uri, $controller)
    {
      if($this->http_method() === 'HEAD')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }

    public function post($uri, $controller)
    {
      if($this->http_method() === 'POST')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }

    public function put($uri, $controller)
    {
      if($this->http_method() === 'PUT')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }

    public function patch($uri, $controller)
    {
      if($this->http_method() === 'PATCH')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }

    public function delete($uri, $controller)
    {
      if($this->http_method() === 'DELETE')
      {
        $this->uri_controller_mapper($uri, $controller);
      }
    }


}
