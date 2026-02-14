<?php

namespace PipraPay;

class GatewayCache
{
    private string $cacheFile;
    private int $ttl;
    private array $cache = [];
    private bool $enabled;

    public function __construct(string $cacheFile = '', int $ttl = 3600, bool $enabled = true)
    {
        $this->cacheFile = $cacheFile ?: sys_get_temp_dir() . '/piprapay_gateway_cache.json';
        $this->ttl = $ttl;
        $this->enabled = $enabled;
        $this->load();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function get(string $key, callable $callback = null)
    {
        if (!$this->enabled) {
            return $callback ? $callback() : null;
        }

        if ($this->has($key) && !$this->isExpired($key)) {
            return $this->cache[$key]['data'];
        }

        if ($callback) {
            $data = $callback();
            $this->set($key, $data);
            return $data;
        }

        return null;
    }

    public function set(string $key, $data, int $ttl = null): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->cache[$key] = [
            'data' => $data,
            'timestamp' => time(),
            'ttl' => $ttl ?? $this->ttl
        ];

        $this->save();
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function isExpired(string $key): bool
    {
        if (!$this->has($key)) {
            return true;
        }

        $cacheEntry = $this->cache[$key];
        $elapsed = time() - $cacheEntry['timestamp'];

        return $elapsed > $cacheEntry['ttl'];
    }

    public function delete(string $key): self
    {
        unset($this->cache[$key]);
        $this->save();
        return $this;
    }

    public function clear(): self
    {
        $this->cache = [];
        $this->save();
        return $this;
    }

    public function getExpiredKeys(): array
    {
        $expired = [];

        foreach ($this->cache as $key => $entry) {
            if ($this->isExpired($key)) {
                $expired[] = $key;
            }
        }

        return $expired;
    }

    public function clearExpired(): self
    {
        foreach ($this->getExpiredKeys() as $key) {
            $this->delete($key);
        }

        return $this;
    }

    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    public function setCacheFile(string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;
        $this->load();
        return $this;
    }

    private function load(): void
    {
        if (!file_exists($this->cacheFile)) {
            $this->cache = [];
            return;
        }

        $content = file_get_contents($this->cacheFile);
        $data = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            $this->cache = $data;
        } else {
            $this->cache = [];
        }
    }

    private function save(): void
    {
        $dir = dirname($this->cacheFile);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $content = json_encode($this->cache, JSON_PRETTY_PRINT);

        if ($content !== false) {
            @file_put_contents($this->cacheFile, $content, LOCK_EX);
        }
    }

    public function getCacheInfo(): array
    {
        $info = [
            'enabled' => $this->enabled,
            'ttl' => $this->ttl,
            'cache_file' => $this->cacheFile,
            'file_exists' => file_exists($this->cacheFile),
            'total_entries' => count($this->cache),
            'expired_entries' => count($this->getExpiredKeys())
        ];

        if ($info['file_exists']) {
            $info['file_size'] = filesize($this->cacheFile);
            $info['file_modified'] = date('Y-m-d H:i:s', filemtime($this->cacheFile));
        }

        return $info;
    }
}
