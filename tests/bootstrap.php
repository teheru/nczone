<?php

spl_autoload_register(function($class) {
    if (strpos($class, 'eru\\nczone\\') !== 0) {
        return;
    }

    $filePath = __DIR__ . '/../' . implode('/', array_slice(explode('\\', $class), 2)) . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
