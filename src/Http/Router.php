<?php

namespace TinyPHP\Http;

use FastRoute;

class Router
{
    private $fastRoute;
    private $groupData;
    private $middleware = [];
    private $routes;

    const FOUND = 1;
    const NOT_FOUND = 2;
    const METHOD_NOT_ALLOWED = 3;

    public function __construct()
    {
        $this->fastRoute = new FastRoute\RouteCollector(
            new FastRoute\RouteParser\Std(), new FastRoute\DataGenerator\GroupCountBased()
        );
    }

    public function match($uri, $method)
    {
        $routeInfo = array();
        $dispatcher = new FastRoute\Dispatcher\GroupCountBased($this->fastRoute->getData());
        $fastRouteInfo = $dispatcher->dispatch($method, $uri);
        switch ($fastRouteInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $routeInfo[0] = self::NOT_FOUND;
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $routeInfo[0] = self::METHOD_NOT_ALLOWED;
                break;
            case FastRoute\Dispatcher::FOUND:
                $routeInfo[0] = self::FOUND;
                $routeInfo[1] = $fastRouteInfo[1];
                $routeInfo[2] = $fastRouteInfo[2];
                break;
        }

        return $routeInfo;
    }

    public function group($prefix, $cb)
    {
        $this->groupData['prefix'] = $prefix;
        call_user_func($cb);
        $this->groupData = null;
    }

    public function useMiddleware($middleware)
    {
        $middleware = (array) $middleware;

        if ($this->groupData) {
            $this->groupData['middleware'] = $middleware;
        } else {
            $this->middleware = $middleware;
        }
    }

    public function getMiddleware()
    {
        return $this->middleware;
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
        $data['action'] = $action;
        $data['middleware'] = [];

        if ($this->groupData) {
            $uri = $this->groupData['prefix'].$uri;
            $middleware = $this->groupData['middleware'];
            $data['middleware'] = $middleware;
        }

        $this->fastRoute->addRoute($method, $uri, $data);
    }
}
