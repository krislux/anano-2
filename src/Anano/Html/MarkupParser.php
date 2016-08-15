<?php namespace Anano\Html;

class MarkupParser
{
    private $text;

    public function __construct($text = null)
    {
        $this->text = trim($text);
    }

    public static function make($text)
    {
        return new self($text);
    }

    public function __toString()
    {
        return $this->paragraphs();
    }

    public function toArray()
    {
        return $this->parse();
    }


    public function loadString($text)
    {
        $this->text = trim($text);
    }

    public function loadFile($file)
    {
        $text = file_get_contents($file);
        $this->text = trim($text);
    }


    /**
     * Print as standard paragraphs with optional classes, headers translated by default.
     */
    public function paragraphs($class = null, array $translators = null)
    {
        if ($translators === null)
            $translators = $this->getDefaultTranslators();

        $lines = $this->parse($translators);

        if ($class) $class = " class=\"$class\"";

        $buffer = '';
        foreach ($lines as &$para)
        {
            if (strpos(current($para), '<h') === false)
            {
                $para = implode("<br>\n", $para);
                $buffer .= "<p$class>$para</p>\n";
            }
            else
            {
                $para = implode("\n", $para);
                $buffer .= $para;
            }
        }

        return $buffer;
    }

    /**
     * Print as div paragraphs with optional classes, headers translated by default.
     * TODO: Work in progress
     */
    public function divs($class = null, array $translators = null)
    {
        if ($translators === null)
            $translators = $this->getDefaultTranslators();

        $lines = $this->parse($translators);

        if ($class) $class = " class=\"$class\"";

        $buffer = '';
        foreach ($lines as &$para)
            $para = implode("<br>\n", $para);
        $buffer = "<div$class>\n\t". implode("\n</div>\n<div$class>\n\t", $lines) ."\n</div>\n\t";

        return $buffer;
    }

    /**
     * Internal parser, splits string into two-dimensional array of paragraphs with lines inside.
     * @param array     $translators   Regular expressions to replace in each line.
     * @param callable  $line_map      Optional function to apply to each line during parsing.
     */
    private function parse(array $translators = null, callable $line_map = null)
    {
        $lines = preg_split('/[\n]/', $this->text);
        $lines = array_map('trim', $lines);

        $output = [];
        $para = [];

        foreach ($lines as $line)
        {
            if ( ! $line)
            {
                $output[] = $para;
                $para = [];
            }
            else
            {
                if ($line_map)
                    $line = $line_map($line);
                if ($translators)
                    $line = $this->translate($line, $translators);

                $para[] = $line;
            }
        }
        $output[] = $para;

        return $output;
    }

    /**
     * Perform translations
     */
    private function translate($str, array $translators)
    {
        foreach ($translators as $pattern => $replace)
        {
            $str = preg_replace($pattern, $replace, $str);
        }
        return $str;
    }

    /**
     * Return default translators for when no others supplied
     */
    private function getDefaultTranslators()
    {
        return [
            '/^#\s?([^\#]+)$/' => '<h1>$1</h1>',
            '/^##\s?([^\#]+)$/' => '<h2>$1</h2>',
            '/^###\s?([^\#]+)$/' => '<h3>$1</h3>',
            '/\[(.+)\]\(([^\s]+)\)/' => '<a href="$2">$1</a>',
        ];
    }
}
