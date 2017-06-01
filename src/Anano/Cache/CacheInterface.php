<?php

/**
 * Interface for simple caching classes.
 * A bit unorthodox, the TTL is supplied in the get-method instead of the put.
 * This allows us to forego saving the expiration time, which makes e.g.
 * file caching simpler. Extensions are of course free to also supply a TTL in
 * the put-method and only use the get parameter as a fallback.
 */

namespace Anano\Cache;

interface CacheInterface
{
    /**
     * Must store a value as $key
     */
    public function put($key, $value);

    /**
     * Must retrieve value from $key.
     * Should return $def if key does not exist.
     * Should return $def or null if value updated more than $minutes ago.
     */
    public function get($key, $def, $minutes);

    /**
     * Must remove value from storage based on $key.
     */
    public function forget($key);

    /**
     * Must remove all values from storage.
     * Should clean up any used resources.
     */
    public function clear();
}
