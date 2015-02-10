<?php

/**
 * Anano Framework
 *
 * @package Anano
 * @author  Kris Lux <email@amunium.dk>
 * @version 2.0
 */

define('ROOT_DIR', __DIR__);
define('STORAGE_DIR', __DIR__ . '/app/storage');

require __DIR__ . '/vendor/autoload.php';

$app = Anano\App::init();
$app->dispatch();