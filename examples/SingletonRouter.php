<?php
namespace MiniRouter\Examples;

use MiniRouter\MiniRouter;

final class SingletonRouter
{
    private static ?MiniRouter $instance = null;

    public static function getRouter(): MiniRouter
    {
        if (self::$instance === null) {
            self::$instance = new MiniRouter();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
