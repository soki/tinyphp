<?php

namespace TinyPHP\Http;

class Router
{
    private $routes = [];

    const FOUND = 1;
    const NOT_FOUND = 2;
    const METHOD_NOT_ALLOWED = 3;

    public function match($uri, $method)
    {
        $routeInfo = [];
        if (!isset($this->routes[$uri])) {
            $routeInfo[0] = self::NOT_FOUND;

            return $routeInfo;
        }

        $route = $this->routes[$uri];
        if (!in_array($method, $route['method'])) {
            $routeInfo[0] = self::METHOD_NOT_ALLOWED;

            return $routeInfo;
        }

        $routeInfo[0] = self::FOUND;
        $routeInfo[1] = $route;

        return $routeInfo;
    }

    public function get($uri, $action)
    {
        $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function pacth($uri, $action)
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    public function any($uri, $action)
    {
        $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

    private function addRoute($method, $uri, $action)
    {
        $this->routes[$uri] = [
            'action' => $action,
            'method' => (array) $method,
        ];
    }
}
