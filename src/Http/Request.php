<?php

namespace TinyPHP\Http;

use Closure;

class Request
{
    public $namespace;
    private $session;

    public function __construct()
    {
    }

    public function input($key = null, $default = null)
    {
        $params = $_GET + $_POST;
        if ($key == null) {
            return $params;
        }

        return isset($params[$key]) ? $params[$key] : $default;
    }

    public function getHeader($key)
    {
        $contentHeaders = ['CONTENT_LENGTH' => null, 'CONTENT_TYPE' => null];
        if (isset($contentHeaders[$key])) {
            return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
        }

        $key = str_replace('-', '_', $key);
        $key = 'HTTP_'.strtoupper($key);
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return;
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getPathInfo()
    {
        $pathInfo = rtrim(preg_replace('/\?.*/', '', $this->uri()), '/');

        return $pathInfo ? $pathInfo : '/';
    }

    public function session($id = '')
    {
        if ($id) {
            session_id($id);
        }

        if ($this->session) {
            return $this->session;
        }
        $this->session = new Session();

        return $this->session;
    }

    public function dispatch($namespace, Closure $callback)
    {
        $this->namespace = $namespace;
        $router = new Router();
        call_user_func($callback, $router);

        $routeInfo = $router->match($this->getPathInfo(), $this->getMethod());

        switch ($routeInfo[0]) {
            case Router::FOUND:
                $response = $this->handleRoute($routeInfo);
                break;
            case Router::NOT_FOUND:
                $response = new Response('Not Found', 404);
                break;
            case Router::METHOD_NOT_ALLOWED:
                $response = new Response('Method Not Allowed', 405);
                break;
        }

        if ($response instanceof Response) {
            $response->send();
        } else {
            echo (string) $response;
        }
    }

    private function handleRoute($routeInfo)
    {
        list($controller, $method) = explode('@', $routeInfo[1]['action']);
        $controller = $this->namespace.'\\'.$controller;
        if (!method_exists($instance = new $controller(), $method)) {
            $response = new Response('Internal Server Error', 500);
        } else {
            $response = app()->call([$instance, $method]);
        }

        return $response;
    }
}
