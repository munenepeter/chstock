<?php

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    if (file_exists(__DIR__ . '/' . $class . '.php')) {
        require_once __DIR__ . '/' . $class . '.php';
    }else {
        throw new Exception("Class $class not found");
    }   
});

require_once __DIR__.'/database/database.php';

require_once __DIR__.'/config.php';