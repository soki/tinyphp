<?php

use Illuminate\Container\Container;
use TinyPHP\Http\Response;

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

function view($name, $data = [], array $headers = [])
{
    return (new Response($data, 200, $headers))->view($name);
}

function redirect($url, array $headers = [])
{
    return (new Response('', 302, $headers))->redirect($url);
}
