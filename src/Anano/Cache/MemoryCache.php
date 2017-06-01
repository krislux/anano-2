<?php namespace Anano\Cache;

use ErrorException;

class MemoryCache implements CacheInterface
{
    private $token;
    private $connection;

    /**
     * @param  int  $token  E.g 0x620086ee - Manually set the ftok to save time. This is a bit faster, but
     *                      potentially dangerous. You need to make sure the given token works on the current system.
     */
    public function __construct($token = null)
    {
        if ( ! function_exists('shm_attach')) {
            throw new ErrorException('To enable the System V shared memory support compile PHP with the option --enable-sysvshm.');
        }

        $this->token = $token ? $token : ftok(__FILE__, 'b');
    }

    public function __destruct()
    {
        if ($this->connection) {
            shm_detach($this->connection);
            $this->connection = null;
        }
    }

    public function get($key, $def = null, $minutes = 10)
    {
        $key = $this->key($key);
        $handle = $this->handle();

        if (shm_has_var($handle, $key)) {
            $buffer = shm_get_var($handle, $key);

            // Extract time
            $pt = substr($buffer, 0, 4);
            $time = current(unpack('l', $pt));
            $value = substr($buffer, 4);

            if ( ! $minutes || $time + ($minutes * 60) > $_SERVER['REQUEST_TIME']) {
                return $value;
            }
        }
        
        return $def;
    }

    public function put($key, $value)
    {
        $key = $this->key($key);

        // Pack the current timestamp along with the value
        $bt = pack('l', $_SERVER['REQUEST_TIME']);
        $value = $bt . $value;

        return shm_put_var($this->handle(), $key, $value);
    }

    public function forget($key)
    {
        $key = $this->key($key);

        shm_remove_var($this->handle(), $key);
    }

    public function clear()
    {
        shm_remove($this->handle());
        $this->connection = null;
    }


    /**
     * Create or retrieve memory handle.
     */
    private function handle()
    {
        if ( ! $this->connection) {
            $this->connection = shm_attach($this->token);
        }

        return $this->connection;
    }

    /**
     * Convert the default string key to format accepted by shm (int).
     * CRC'ing it is currently the best, not ridiculously complicated way
     * of doing that, but be aware of the possibility of collisions.
     */
    private function key($key)
    {
        if (is_int($key)) {
            return $key;
        }
        return crc32($key);
    }
}