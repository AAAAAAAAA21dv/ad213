<?php
// Router script to disable host header check for PHP built-in server

if (php_sapi_name() == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false; // Serve the requested resource as-is.
    }
}

// Fallback to index.php or requested script
require_once __DIR__ . '/index.php';
?>
