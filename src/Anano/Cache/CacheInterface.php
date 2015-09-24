<?php

namespace Anano\Cache;

interface CacheInterface
{
    public function put($key, $content);

    public function get($key, $def, $minutes);
}
