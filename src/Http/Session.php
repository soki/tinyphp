<?php

namespace TinyPHP\Http;

use Exception;

class Session
{
    private $data;
    private $change = false;
    private $handler = [
        'files' => 'fileHandler',
    ];

    public function __construct()
    {
        $cfg = config('app.session');
        if (empty($cfg)) {
            throw new Exception('app.session is not set', 1);
        }

        $saveHandler = $cfg['save_handler'];
        session_name($cfg['name']);

        if (isset($this->handler[$saveHandler])) {
            $handler = $this->handler[$saveHandler];
            $this->$handler($cfg);
        } else {
            $this->setHandler(new $saveHandler());
        }

        session_start();

        $this->data = $_SESSION;
    }

    public function setHandler(SessionHandlerInterface $handler)
    {
        session_set_save_handler($handler);
    }

    private function fileHandler($config)
    {
        ini_set('session.gc_maxlifetime', $config['maxlifetime']);
        ini_set('session.save_path', $config['save_path']);
        ini_set('session.save_handler', 'files');
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
