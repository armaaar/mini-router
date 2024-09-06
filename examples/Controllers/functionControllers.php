<?php

namespace MiniRouter\Examples\Controllers;

function simpleController(): void
{
    echo "This page is generated using a controller function";
}

function simpleViewController(): void
{
    include "views/simpleView.html";
}
