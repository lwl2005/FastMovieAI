<?php

namespace plugin\control\utils;

class LRUCache
{
    private int $capacity;
    private array $cache = [];
    private array $order = [];

    public function __construct($capacity = 2000)
    {
        $this->capacity = $capacity;
    }

    public function get($key)
    {
        if (!isset($this->cache[$key])) {
            return null;
        }
        unset($this->order[$key]);
        $this->order[$key] = true;
        return $this->cache[$key];
    }

    public function set($key, $value)
    {
        if (isset($this->cache[$key])) {
            $this->cache[$key] = $value;
            unset($this->order[$key]);
            $this->order[$key] = true;
            return;
        }

        if (count($this->cache) >= $this->capacity) {
            $oldestKey = array_key_first($this->order);
            unset($this->cache[$oldestKey], $this->order[$oldestKey]);
        }

        $this->cache[$key] = $value;
        $this->order[$key] = true;
    }
}
