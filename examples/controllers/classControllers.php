<?php

class UserControllers
{

  private $username;

  function __construct($username = "anonymous")
  {
    $this->username = $username;
  }

  public function helloUser() {
    echo "Hello, ".$this->username."!";
  }

  public function settingsView() {
    echo "You can change your settings from this page. You better be authenticated!";
  }

  public function controlUserLife() {
    echo "I own you now ".$this->username."! MUA HA HA HA!!";
  }
}
