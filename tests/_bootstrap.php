<?php
// This is global bootstrap for autoloading

define('ROOT_DIR', __DIR__ . '/..');
define('STORAGE_DIR', __DIR__ . '/../app/storage');

require __DIR__ . '/../vendor/autoload.php';

Anano\App::init();