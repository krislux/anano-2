<?php

namespace Anano\Cache;

use ErrorException;

class FileCache implements CacheInterface
{
    const DIR = '/app/storage/cache/file/';
    const SERIALIZED_HEADER = "\xff";

    private $compress;

    /**
     * @param  bool     $compress    Whether to gzip the data before storage. Slower but smaller.
     */
    public function __construct($compress = false)
    {
        if( ! is_writable(ROOT_DIR . self::DIR) )
        {
            mkdir(ROOT_DIR . self::DIR);
            chmod(ROOT_DIR . self::DIR, 755);
        }

        $this->compress = $compress;
    }

    public function put($key, $content)
    {
        $path = $this->createFilePath($key);

        if ( ! is_string($content))
            $content = self::SERIALIZED_HEADER . serialize($content);

        if ($this->compress)
            $content = gzcompress($content);

        $fh = fopen($path, 'w');
        fwrite($fh, $content);
        fclose($fh);
    }

    public function get($key, $def = null, $minutes = 10)
    {
        $path = $this->createFilePath($key);
        $content = $def;

        if ( file_exists($path) )
        {
            $mod = filemtime($path);

            if ($mod + ($minutes * 60) > time())
            {
                try
                {
                    $fh = fopen($path, 'r');
                    $content = fread($fh, filesize($path));
                    fclose($fh);

                    if ($this->compress)
                        $content = gzuncompress($content);

                    if ($content[0] === self::SERIALIZED_HEADER)
                        $content = unserialize( substr($content, 1) );
                }
                catch (ErrorException $e)
                {
                    $content = $def;
                }
            }
            else
            {
                unlink($path);
            }
        }

        return $content;
    }

    public function clear()
    {
        $g = glob(ROOT_DIR . self::DIR . '*');
        foreach ($g as $file)
            unlink($file);
    }

    public function forget($key)
    {
        $path = $this->createFilePath($key);
        if (file_exists($path))
            unlink($path);
    }

    private function createFilePath($key)
    {
        return ROOT_DIR . self::DIR . md5($key);
    }
}
