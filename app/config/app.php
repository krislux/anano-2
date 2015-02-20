<?php

/**
 * The main configuration file for your project. You should always look through this.
 *
 * As with the all config files, you can make environment-specific files without touching the
 * main file by creating a subfolder in app/config named as your machine name in lowercase and
 * copying this file in there. Changes made to this file will apply only on your local server.
 *
 * Feel free to add your own settings and retrieve them with Config::get('yourconfigfile.yoursetting')
 */

return array(
    
    /**
     * Toggle global debug mode on/off.
     * 
     * When debugging is on, more error information is displayed, templates are generated every time regardless if they are changed,
     * etc. It is strongly recommended to turn this off in a production environment.
     */
    
    'debug' => true,
    
    
    /**
     * Toggle displaying information about generation time, memory use, etc.
     * 
     * Also requires debugging to be enabled. Note that it breaks standard HTML and may force some browsers into quirks mode.
     */
    
    'profile' => true,
    
    
    /**
     * The name of the session cookie. Default will work fine, but you may want to set this to something related to your project.
     *
     * Set to null to disable sessions for the project. Bear in mind this will also disable csrf protection.
     */
    
    'session' => 'anano_session',
    
    
    /**
     * Default timezone for date and time functions.
     * 
     * http://php.net/manual/en/timezones.php
     **/
    
    'timezone' => 'Europe/Copenhagen',
    
    
    /**
     * The start and end tags for template code. You may wish to change these if you include e.g.
     * AngularJs, which uses the same tags. These also affect template comments.
     */
    
    'template-tags' => array('{{', '}}'),
    
    
    /**
     * Bindings for the simple included IoC container.
     */
    
    'binds' => array(
        //'Router' => 'Anano\\Routing\\Router',
        'Router' => 'Anano\\Router',
        'Database' => 'Anano\\Database\\ORM\\Database',
        'QueryBuilder' => 'Anano\\Database\\ORM\\QueryBuilder',
    ),
);