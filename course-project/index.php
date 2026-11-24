<?php
declare(strict_types=1);

$loader = is_file(__DIR__ . '/vendor/autoload.php')
    ? __DIR__ . '/vendor/autoload.php'
    : __DIR__ . '/flight/autoload.php';
require $loader;
require __DIR__ . '/data.php';
require __DIR__ . '/app.php';

$app = new flight\Engine();
$app->set('flight.views.path', __DIR__);
$app->set('flight.allow_method_override', false);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
$controller = new BlogController($app, $base);

$app->route('GET /', [$controller, 'home']);
$app->route('GET /index.php', [$controller, 'home']);
$app->route('GET /articles', [$controller, 'catalog']);
$app->route('GET /articles/@slug:[a-z-]+', [$controller, 'article']);
$app->route('GET /planner', [$controller, 'planner']);
$app->route('GET /feedback', [$controller, 'feedback']);
$app->route('POST /feedback', [$controller, 'submitFeedback']);
$app->map('notFound', [$controller, 'notFound']);
$app->start();
