<?php

// Update these for your local MySQL setup.
// On Railway (or any server), set these as environment variables.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'qr_table');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// App settings
define('APP_NAME', getenv('APP_NAME') ?: 'QR Table Ordering');

