<?php
namespace MiniRouter\Examples\Controllers;

class UserControllers
{

    private string $username;

    function __construct(string $username = "anonymous")
    {
        $this->username = $username;
    }

    public function helloUser(): void
    {
        echo "Hello, ".$this->username."!";
    }

    public static function staticHelloUser(string $username = "static user"): void
    {
        echo "Hello, ".$username."!";
    }

    public function settingsView(): void
    {
        echo "You can change your settings from this page. You better be authenticated!";
    }

    public function controlUserLife(): void
    {
        echo "I own you now ".$this->username."! MUA HA HA HA!!";
    }
}
