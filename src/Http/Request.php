<?php

namespace TinyPHP\Http;

use Closure;

class Request
{
    private $namespace = [];
    private $middleware = [];
    private $session;
    private $controllerAction;

    public function __construct()
    {
    }

    public function input($key = null, $default = '')
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

    public function getContent()
    {
        return file_get_contents('php://input');
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

    public function setNamespace($data)
    {
        $this->namespace = array_merge($this->namespace, $data);
    }

    public function dispatch($namespace, Closure $callback)
    {
        $this->namespace['controller'] = $namespace;
        $router = new Router();
        call_user_func($callback, $router);

        $routeInfo = $router->match($this->getPathInfo(), $this->getMethod());

        switch ($routeInfo[0]) {
            case Router::FOUND:
                $response = $this->handle($router->getMiddleware(), $routeInfo);
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

    public function next()
    {
        if (!empty($this->middleware)) {
            $handle = array_shift($this->middleware);
            $namespace = $this->namespace['middleware'];
            $middleware = $namespace.'\\'.$handle;

            return (new $middleware())->handle($this);
        } else {
            $action = $this->controllerAction;

            return $action();
        }
    }

    private function handle($middleware, $routeInfo)
    {
        if (!empty($routeInfo[1]['middleware'])) {
            $middleware = array_merge($middleware, $routeInfo[1]['middleware']);
        }
        $this->middleware = $middleware;

        $this->controllerAction = function () use ($routeInfo) {
            return $this->handleRoute($routeInfo);
        };

        return $this->next();
    }

    private function handleRoute($routeInfo)
    {
        $action = $routeInfo[1]['action'];
        $params = $routeInfo[2];

        if (!is_callable($action)) {
            $namespace = $this->namespace['controller'];
            list($controller, $method) = explode('@', $action);
            $controller = $namespace.'\\'.$controller;
            if (!method_exists($instance = new $controller(), $method)) {
                return new Response('Internal Server Error', 500);
            } else {
                $callable = [$instance, $method];
            }
        } else {
            $callable = $action;
        }

        array_unshift($params, $this);

        return call_user_func_array($callable, $params);
    }
}
