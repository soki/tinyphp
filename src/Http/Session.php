<?php

namespace TinyPHP\Http;

use Exception;

class Session
{
    private $data;
    private $change = false;
    private $handler = [
            'files' => 'fileHandler',
            'redis' => 'redisHandler',
    ];

    public function __construct()
    {
        $session = config('app.session');
        if (empty($session)) {
            throw new Exception('app.session is not set', 1);
        }

        $saveHandler = $session['save_handler'];
        session_name($session['name']);
        ini_set('session.gc_maxlifetime', $session['maxlifetime']);
        ini_set('session.save_path', $session['save_path']);

        if (isset($this->handler[$saveHandler])) {
            $handler = $this->handler[$saveHandler];
            $this->$handler();
        }

        session_start();

        $this->data = $_SESSION;
    }

    public function setHandler(SessionHandlerInterface $handler)
    {
        session_set_save_handler($handler);
    }

    private function fileHandler()
    {
        ini_set('session.save_handler', 'files');
    }

    private function redisHandler()
    {
        session_set_save_handler(new RedisSessionHandler());
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data;
        }

        return;
    }

    public function set($v, $value = null)
    {
        if (is_array($v)) {
            foreach ($v as $key => $val) {
                $this->data[$key] = $val;
            }
        } else {
            $this->data[$v] = $value;
        }

        $this->change = true;
    }

    public function remove($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            $this->change = true;
        }
    }

    public function all()
    {
        return $this->data;
    }

    public function has($key)
    {
        if (isset($this->data[$key])) {
            return true;
        }

        return false;
    }

    public function clear()
    {
        $this->data = [];
        $this->change = true;
    }

    public function __destruct()
    {
        if ($this->change) {
            $_SESSION = $this->data;
            $this->change = false;
        }
    }
}
