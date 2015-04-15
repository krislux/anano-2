<?php

namespace Anano;

class Template
{
    protected $buffer;
    
    public function __construct($buffer)
    {
        $this->buffer = $buffer;
    }
    
    /**
     * Process template code, convert it to php code and return it to be cached.
     * In debug mode this happens on every view, but will otherwise only occur when a source file changes.
     */
    
    public function process()
    {
        $buffer = &$this->buffer;
        
        $tags = Config::get('app.template-tags');
        
        // Replace rooted URLs with correctly rooted URLs, even when site is in subfolders.
        $buffer = preg_replace('/((href|src|action)=(\"|\'))\/([^\'\"]*)(\"|\')/', '$1<?php echo App::root(); ?>/$4$5', $buffer);
        $buffer = preg_replace('/@approot([^\w]*)/', '<?php echo App::root(); ?>$1', $buffer);
        $buffer = preg_replace('/@token([^\w]*)/', '<?php echo token(); ?>$1', $buffer);
        
        $buffer = preg_replace('/^[\s]*\@(extends|layout|master)[ \t]+([\w\.\/_-]+)[\s]*$/m', '<?php $this->setLayout("$2"); ?>' . "\r\n", $buffer);
        
        $buffer = preg_replace('/^[\s]*\@(content|render|RenderBody)[\s]*$/m', '<?php echo $viewContent; ?>' . "\r\n", $buffer);
        
        $buffer = preg_replace('/^[\s]*\@(include|partial)[ \t]+([\w\.\/_-]+)[\s]*$/m', '<?php echo new View("$2", isset($data) ? $data : null); ?>' . "\r\n", $buffer);
        
        // Single-line executions with @
        $buffer = preg_replace_callback('/^[\s]*\@(.+)$/m', function($parts) {
            return '<?php '. trim($parts[1], " \t\r\n;") .' ?>';
        }, $buffer);
        
        // Comments
        $buffer = preg_replace('/'. $tags[0] .'--.*?--'. $tags[1] .'[\r\n\f]*/s', '', $buffer);
        
        // Echo code
        $buffer = preg_replace_callback('/'. $tags[0] .'(.*?)'. $tags[1] .'/s', function($parts) {
            $part = trim($parts[1], " \t;");
            if (preg_match('/^(\$[\w\-_>\[\]\']+)[ \t]+(or|\|)[ \t]+(.*)$/', $part, $matches))
                return "<?php echo isset({$matches[1]}) ? {$matches[1]} : {$matches[3]}; ?>";
            return '<?php echo '. $part .'; ?>';
        }, $buffer);
        
        return $buffer;
    }
}
