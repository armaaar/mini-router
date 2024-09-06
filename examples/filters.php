<?php

namespace MiniRouter\Examples;

class Filters
{
    static bool $isLoggedIn = true;
    static bool $isAdmin = false;

    static function addFilters(): void {
        $router = SingletonRouter::getRouter();

        $router->filter("is_user", function(){
            if(self::$isLoggedIn)
                return true;
            return false;
        });


        $router->filter("is_admin", function(){
            if(self::$isAdmin)
                return true;
            return false;
        });
    }
}
