<?php

/**
 * Database connection settings for both internal ORM and ActiveRecord.
 */

return array(
    
    'default' => 'mysql',
    
    'connections' => array(
        
        'mysql' => array(
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'Hest123!',
            'database' => 'test',
            'charset'  => 'utf8',
        )
    ),
);