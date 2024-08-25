<?php

namespace MiniRouter\Examples\Controllers;

function simpleController()
{
    echo "This page is generated using a controller function";
}

function simpleViewController()
{
    include "views/simpleView.html";
}
