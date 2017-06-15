<?php

/**
 * Settings for console interface.
 * To use, type `php run` from a terminal in the project root.
 */

return array(
    
    /**
     * Directories to look for command files. Prioritized, top first.
     */

    'command_dirs' => array(
        ROOT_DIR . '/app/commands',
        ROOT_DIR . '/src/Anano/Console/Commands',
    ),


    /**
     * Directories to look for templates used in commands.
     * Prioritized, top first. Ignored if a full path is passed in Template::__construct().
     */

    'template_dirs' => array(
        ROOT_DIR . '/src/Anano/Console/Templates',
    ),


    /**
     * Directories to look migration files.
     * By default, new migrations will be placed in the first listed dir.
     */

    'migration_dirs' => array(
        ROOT_DIR . '/app/database/migrations',
    )
);