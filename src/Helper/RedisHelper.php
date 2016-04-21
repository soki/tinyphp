<?php

namespace TinyPHP\Helper;

use Exception;
use Redis;

class RedisHelper
{
    private $_redis;

    public function __construct($config, $p = false)
    {
        $redis = new Redis();
        if ($p) {
            $result = $redis->pconnect($config['host'], $config['port'], $config['timeout']);
        } else {
            $result = $redis->connect($config['host'], $config['port'], $config['timeout'], null, 100);
        }
        if (!$result) {
            throw new Exception('redis connect error'.$config['host'].':'.$config['port'], 1);
        }
        $this->_redis = $redis;
    }

    public function getRedis()
    {
        return $this->_redis;
    }

    public function multi()
    {
        return $this->redis->multi();
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $data)
    {
        return $this->redis->set($key, $data);
    }

    public function setNx($key, $data)
    {
        return $this->redis->setNx($key, $data);
    }

    public function setEx($key, $expire, $data)
    {
        return $this->redis->setEx($key, $expire, $data);
    }

    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    public function getIncr($key)
    {
        return $this->redis->get($key);
    }

    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    public function mGet($keys)
    {
        return $this->redis->mGet($keys);
    }

    public function mSet($data)
    {
        return $this->redis->mSet($data);
    }

    public function mSetNx($data)
    {
        return $this->redis->mSetNx($data);
    }

    public function lPush($key, $value)
    {
        return $this->redis->lPush($key, $value);
    }

    public function rPush($key, $value)
    {
        return $this->redis->rPush($key, $value);
    }

    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    public function lSet($key, $idx, $value)
    {
        return $this->redis->lSet($key, $idx, $value);
    }

    public function lGet($key, $idx)
    {
        return $this->redis->lGet($key, $idx);
    }

    public function lGetAll($key)
    {
        return $this->redis->lRange($key, 0, -1);
    }

    public function lRange($key, $start, $end)
    {
        return $this->redis->lRange($key, $start, $end);
    }

    public function lTrim($key, $start, $end)
    {
        return $this->redis->lTrim($key, $start, $end);
    }

    public function lSize($key)
    {
        return $this->redis->lSize($key);
    }

    public function lRem($key, $value, $count)
    {
        return $this->redis->lRem($key, $value, $count);
    }

    public function hSet($key, $field, $value, $nx = false)
    {
        if ($nx) {
            return $this->redis->hSetNx($key, $field, $value);
        } else {
            return $this->redis->hSet($key, $field, $value);
        }
    }

    public function hGet($key, $field)
    {
        return $this->redis->hGet($key, $field);
    }

    public function hDel($key, $field)
    {
        return $this->redis->hDel($key, $field);
    }

    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    public function hExists($key, $field)
    {
        return $this->redis->hExists($key, $field);
    }

    public function hMSet($key, $data)
    {
        return $this->redis->hMSet($key, $data);
    }

    public function hMGet($key, $fields)
    {
        return $this->redis->hMGet($key, $fields);
    }

    public function hIncrBy($key, $field, $value)
    {
        $this->redis->hIncrBy($key, $field, $value);
    }

    public function zAdd($key, $score, $value)
    {
        return $this->redis->zAdd($key, $score, $value);
    }

    public function zIncrBy($key, $score, $value)
    {
        return $this->redis->zIncrBy($key, $score, $value);
    }

    public function zGetAll($key)
    {
        return $this->redis->zRange($key, 0, -1);
    }

    public function zRange($key, $start, $end, $rev = true, $score = true)
    {
        if (!$rev) {
            return $this->redis->zRange($key, $start, $end, $score);
        } else {
            return $this->redis->zRevRange($key, $start, $end, $score);
        }
    }
    /*
     *   $opt = ['withscores'=>true, 'limit'=>[$offset, $count]]
     */
    public function zRangeByScore($key, $sscore, $escore, $opt = [])
    {
        return $this->redis->zRangeByScore($key, $sscore, $escore, $opt);
    }

    public function zCount($key, $start, $end)
    {
        return $this->redis->zCount($key, $start, $end);
    }

    public function zSize($key)
    {
        return $this->redis->zSize($key);
    }

    public function zScore($key, $value)
    {
        return $this->redis->zScore($key, $value);
    }

    public function zDelete($key, $value)
    {
        return $this->redis->zDelete($key, $value);
    }

    public function zRank($key, $value)
    {
        return $this->redis->zRank($key, $value);
    }

    public function zRevRank($key, $value)
    {
        return $this->redis->zRevRank($key, $value);
    }

    public function zRemRangeByRank($key, $start, $end)
    {
        return $this->redis->zRemRangeByRank($key, $start, $end);
    }

    public function publish($chan, $value)
    {
        return $this->redis->publish($chan, $value);
    }

    public function subscribe($chans, $callback)
    {
        return $this->redis->subscribe($chans, $callback);
    }

    public function pubSub()
    {
    }

    public function info()
    {
        return $this->redis->info();
    }
}
