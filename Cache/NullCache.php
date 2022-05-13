<?php

namespace Bookboon\ApiBundle\Cache;

use Psr\SimpleCache\CacheInterface;

class NullCache implements CacheInterface
{

    public function get($key, $default = null)
    {
        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        return true;
    }

    public function delete($key)
    {
        return true;
    }

    public function clear()
    {
        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        $ret = [];
        foreach ($keys as $key) {
            $ret[$key] = $default;
        }

        return $ret;
    }

    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    public function deleteMultiple($keys)
    {
        return true;
    }

    public function has($key)
    {
        return false;
    }
}
