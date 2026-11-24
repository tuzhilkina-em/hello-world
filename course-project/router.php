<?php
if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/style.css') {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
