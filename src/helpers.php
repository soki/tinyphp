<?php

use Illuminate\Container\Container;

function app($make = null, $parameters = [])
{
    if (is_null($make)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($make, $parameters);
}

function config($key = null, $default = null)
{
    $v = app()->config($key);

    return $v === null ? $default : $v;
}
