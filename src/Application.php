<?php

namespace TinyPHP;

use Illuminate\Container\Container;

define('DS', DIRECTORY_SEPARATOR);

class Application extends Container
{
    protected $basePath;
    private $yaconf = false;
    private $configPath;
    private $configItems = [];

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->bootstrap();
    }

    protected function bootstrap()
    {
        static::setInstance($this);

        $this->singleton("TinyPHP\Http\Request");

        $this->registerContainerAliases();

        $this->configure('app');

        date_default_timezone_set(config('app.timezone', 'Asia/Shanghai'));

        if (config('app.debug', false)) {
            ini_set('display_errors', 'On');
        } else {
            ini_set('display_errors', 'off');
        }
        error_reporting(E_ALL);
    }

    public function basePath($path = '')
    {
        return $this->basePath.($path ? DS.$path : $path);
    }

    public function setConfigPath($path)
    {
        $this->configPath = $path;
    }

    public function appPath()
    {
        return $this->basePath('app');
    }

    public function resPath()
    {
        return $this->basePath('resources');
    }

    public function configure($name)
    {
        if ($this->yaconf) {
            return;
        }

        if ($this->configPath) {
            $dir = rtrim($this->configPath, DS);
        } else {
            $dir = $this->basePath('config');
        }
        $file = $dir.DS.$name.'.php';
        $config = require $file;
        $this->configItems[$name] = $config;
    }

    public function config($key)
    {
        if ($this->yaconf) {
            return Yaconf::get($key);
        } else {
            list($name, $key) = explode('.', $key);
            if (isset($this->configItems[$name][$key])) {
                return $this->configItems[$name][$key];
            }
        }

        return;
    }

    protected function registerContainerAliases()
    {
        $this->aliases = [
            'request' => 'TinyPHP\Http\Request',
        ];
    }
}
