<?php

// Simple front controller that delegates to `public/index.php`.
// This allows hosting the project with the webserver's document root
// pointing to the project root while still using Laravel's `public/` folder
// for public assets and the framework front controller.

// If using PHP's builtin server, we can still serve static files from public/
if (php_sapi_name() === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $publicPath = __DIR__ . '/public' . $uri;

    if ($uri !== '/' && file_exists($publicPath)) {
        return false; // serve the requested resource as-is
    }
}

require_once __DIR__ . '/public/index.php';
