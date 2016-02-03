<?php

namespace Anano\Response;

use ErrorException;
use Anano\Config;
use Anano\Template;

class View extends Response {

    protected $layout;
    protected $attached_data = array();
    protected static $shared_data = array();

    private $process;
    private $file;
    private $data;

    /**
     * @param   string  $file       View file (relative from /views folder, no extension) to load
     * @param   array   $data       Variables to pass to the view
     * @param   bool    $process    Pass file through template processing or not. Not is faster, but make sure there's no template code.
     */

    public function __construct($file, array $data=array(), $process=true)
    {
        $data = array_merge(self::$shared_data, $data);

        $this->file = $file;
        $this->data = $data;
        $this->process = $process;
    }

    public static function exists($file)
    {
        return file_exists( ROOT_DIR . "/app/views/$file.php" );
    }

    public static function make($file, array $data=array())
    {
        return new self($file, $data);
    }

    public static function share($a, $b=null)
    {
        if (is_array($a))
            self::$shared_data = array_merge(self::$shared_data, $a);
        else
            self::$shared_data[$a] = $b;
    }

    public function attach($content, $where = 'after')
    {
        if ( isset($this->attached_data[$where]) )
            $this->attached_data[$where] .= $content;
        else
            $this->attached_data[$where] = $content;

        return $this;
    }

    /**
     * Load and execute template
     *
     * @param   string  $file       View file (relative from /views folder, no extension) to load
     * @param   array   $data       Associative array of variables to pass to the view
     */

    public function render($file, array $data=array())
    {
        $cachedir = ROOT_DIR ."/app/storage/cache/views/";

        if ( ! is_dir($cachedir))
            mkdir($cachedir, 0666, true);

        if ( ! is_writable($cachedir))
            throw new ErrorException('Please make sure /app/storage and all subfolders are configured for writing');

        $token = md5($file);
        $cache = $cachedir . $token . ".php";
        $source = ROOT_DIR . "/app/views/$file.php";

        $debug = Config::get('app.debug');

        if ( ! file_exists($cache) || filemtime($cache) < filemtime($source) || $debug)
        {
            $buffer = file_get_contents($source);

            $template = new Template($buffer);
            $buffer = $template->process();

            file_put_contents($cache, $buffer);
        }

        // Render the page in an anonymous function to create as narrow scope as possible.
        $buffer = call_user_func(function() use ($data, $cache)
        {
            // Use convoluted var name to decrease likelihood of being overwritten by controller. There's probably a better solution, but I don't know it.
            $anano_cache_file_path = $cache;

            ob_start();
            extract($data);
            include $anano_cache_file_path;
            return ob_get_clean();
        });

        if ( isset($this->attached_data['before']) )
            $buffer = $this->attached_data['before'] . $buffer;
        if ( isset($this->attached_data['after']) )
            $buffer .= $this->attached_data['after'];

        if ($this->layout)
        {
            $data['viewContent'] = $buffer;
            $layout = new View($this->layout, $data);
            $buffer = (string)$layout;
        }

        $this->value = $buffer;
    }

    /**
     * Render a view with no template processing. Faster if you don't need it, just make sure there's no template code in it.
     *
     * @param   string  $file       View file (relative from /views folder, no extension) to load
     */

    public function passthru($file, array $data=array())
    {
        ob_start();
        extract($data);
        include $source = ROOT_DIR . "/app/views/$file.php";;
        $this->value = ob_get_clean();
    }

    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }


    public function __toString()
    {
        try
        {
            if ($this->process)
                $this->render($this->file, $this->data);
            else
                $this->passthru($this->file, $this->data);

            return $this->value;
        }
        catch(\Exception $e)
        {
            die($e->getMessage());
        }
    }
}
