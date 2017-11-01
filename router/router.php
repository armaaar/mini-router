<?php

class RouterBrain
{
    // essential functions
    public function get_uri()
    {
      return explode('?', $_SERVER['REQUEST_URI'], 3)[0];
    }

    public function http_method()
    {
      return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    // map URI to Controller
    private function uri_controller_mapper($uri, $controller)
    {
      $current_uri = $this->get_uri();
      if($uri == $current_uri || $uri == $current_uri.'/')
      {
        $controller();
      }
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
