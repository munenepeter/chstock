<?php

$config = require_once __DIR__ . '/../config.php';

$database_file = dirname(__FILE__) . "/database.db";

$is_sqlite = $config['database']['driver'] == 'sqlite';

if ($is_sqlite && !file_exists($database_file)) {
    touch($database_file);
}

try {
    if ($is_sqlite) {
        $db = new PDO("sqlite:" . $database_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } elseif ($config['database']['driver'] == 'mysql') {
        $db = new PDO(
            "mysql:host={$config['database']['host']};dbname={$config['database']['database']};charset=utf8mb4",
            $config['database']['username'],
            $config['database']['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } else {
        throw new InvalidArgumentException("Unsupported database driver: " . $config['database']['driver']);
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!$db || is_null($db)) {
    die("Failed to connect to database.");
}

return $db;
