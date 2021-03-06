<?php

namespace Anano;

class Template
{
    protected $buffer;

    public function __construct($buffer)
    {
        $this->buffer = $buffer;

        $this->keywords = [
            'if',     'else',     'elseif',   'endif',
            'for',    'endfor',   'foreach',  'endforeach',
            'while',  'endwhile',
        ];
    }

    /**
     * Process template code, convert it to php code and return it to be cached.
     * In debug mode this happens on every view, but will otherwise only occur when a source file changes.
     */

    public function process()
    {
        $buffer = &$this->buffer;

        $tags = Config::get('app.template-tags');
        $sets = array(); // Set variables

        // Replace rooted URLs with correctly rooted URLs, even when site is in subfolders.
        $buffer = preg_replace('/((href|src|action)=(\"|\'))\/(?!\/)([^\'\"]*)(\"|\')/', '$1<?php echo App::root(); ?>/$4$5', $buffer);
        $buffer = preg_replace('/@approot([^\w]*)/', '<?php echo App::root(); ?>$1', $buffer);
        $buffer = preg_replace('/@token([^\w]*)/', '<?php echo token(); ?>$1', $buffer);

        $buffer = preg_replace('/^[\s]*\@(extends|layout|master)[ \t]+([\w\.\/_-]+)[\s]*$/m', '<?php $this->setLayout("$2"); ?>' . "\r\n", $buffer);

        $buffer = preg_replace('/^[\s]*\@(content|render|RenderBody)[\s]*$/m', '<?php echo $viewContent; ?>' . "\r\n", $buffer);

        $buffer = preg_replace('/^[\s]*\@(include|partial)[ \t]+([\w\.\/_-]+)[\s]*$/m', '<?php echo new View("$2", isset($data) ? $data : null); ?>' . "\r\n", $buffer);

        $buffer = preg_replace_callback('/^[\s]*\@set[ \t]+\$?([\w\_]+)[ \t](.*)$/m', function($parts) use (&$sets) {
            $sets[$parts[1]] = $parts[2];
            return '';
        }, $buffer);

        // Single-line executions with @
        $buffer = preg_replace_callback('/^[\s]*\@(.+)$/m', function($parts) {
            if (preg_match('/[a-z]+/i', $parts[1], $match))
            {
                $match = current($match);
                if (function_exists($match) || in_array($match, $this->keywords))
                    return '<?php '. trim($parts[1], " \t\r\n;") .' ?>';
            }
            return $parts[0];
        }, $buffer);

        // Comments
        $buffer = preg_replace('/'. $tags[0] .'--.*?--'. $tags[1] .'[\r\n\f]*/s', '', $buffer);

        // MarkupParser parses a simple Markdown-style syntax into HTML.
        $buffer = preg_replace_callback('/'. $tags[0] .'#(.*?)#'. $tags[1] .'/s', function($parts) {
            $part = trim($parts[1], " \t;");
            return \Anano\Html\MarkupParser::make($part)->paragraphs();
        }, $buffer);

        // Echo code
        $buffer = preg_replace_callback('/'. $tags[0] .'(.*?)'. $tags[1] .'/s', function($parts) {
            $part = trim($parts[1], " \t;");
            if (preg_match('/^(\$[\w\-_>\[\]\']+)[ \t]+(or|\|)[ \t]+(.*)$/', $part, $matches))
                return "<?php echo isset({$matches[1]}) ? {$matches[1]} : {$matches[3]}; ?>";
            return '<?php echo '. $part .'; ?>';
        }, $buffer);

        if ( ! empty($sets))
        {
            $temp = "\n<?php\n";
            foreach ($sets as $key => $var)
            {
                $set = $this->parseSetVariable($key, $var);
                $temp .= "self::share('$key', ". $set .");\n";
            }
            $temp .= "?>";

            $buffer .= $temp;
        }

        return $buffer;
    }

    /**
     * Parses @set variables from views to actual PHP variables.
     * @set variables can be numbers, true/false/null or strings with or without quotes. Arrays and objects not supported.
     * $-sign before variables is optional. A few examples:
     *     @set variableOne true
     *     @set $test "This is a test string"
     * These are set at the very top of the parsed template, so you can set variables in a master file from a partial, etc.
     */

    private function parseSetVariable($name, $value)
    {
        $value = trim($value);
        if (is_numeric($value))
            $output = floatval($value);
        elseif (in_array(strtolower($value), array('true', 'false', 'null')))
            $output = $value;
        elseif (preg_match('/[\'\"]{1}(.*)[\'\"]{1}/', $value, $matches))
            $output = '"'. addslashes($matches[1]) .'"';
        else
            $output = '"'. addslashes($value) .'"';
        return $output;
    }
}
