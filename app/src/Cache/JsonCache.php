<?php

namespace Cache;

class JsonCache
{
    /**
     * Seconds to cache expires
     * @var int
     */
    private int $expire;

    private string $file = DIR_CACHE . 'json-cache.json';

    private array $data = [];

    public function __construct(int $expire = 86400)
    {
        $this->expire = $expire;

        if (file_exists($this->file)) {
            $time = filectime($this->file) + $this->expire;
            if ($time <= time()) {
                unlink($this->file);
            } else {
                $this->data = json_decode(file_get_contents($this->file), true) ?? [];
            }
        }
    }

    public function get(string $key): mixed
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return false;
    }

    public function set(string $key, mixed $value): bool
    {
        $this->delete($key);

        $this->data[$key] = $value;

        return true;
    }

    public function delete(string $key): int
    {
        if ($this->get($key)) {
            unset($this->data[$key]);
        }

        return 1;
    }

    public function __destruct()
    {
        if (!is_dir(DIR_CACHE)) {
            mkdir(DIR_CACHE, 0777,true);
        }

        file_put_contents($this->file, json_encode($this->data));
    }
}