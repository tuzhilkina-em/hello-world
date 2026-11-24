<?php
declare(strict_types=1);

require __DIR__ . '/GreetingController.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
if ($base !== '' && str_starts_with($path, $base . '/')) {
    $path = substr($path, strlen($base));
}

$home = in_array($path, ['/', '/index.php'], true);
if ($home) {
    $message = 'Домашняя работа с роутингом';
} elseif (preg_match('~^/(hello|bye)/([^/]+)/?$~D', $path, $matches)) {
    $name = trim(rawurldecode($matches[2]));
    if ($name !== '' && strlen($name) <= 240 && !str_contains($name, '/') && preg_match('//u', $name)) {
        $controller = new GreetingController();
        if ($matches[1] === 'hello') {
            $message = $controller->sayHello($name);
            $title = 'Страница приветствия';
        } else {
            $message = $controller->sayBye($name);
        }
    }
}
if (!isset($message)) {
    http_response_code(404);
    $message = 'Страница не найдена';
    $title = $message;
}
$escape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
require __DIR__ . '/view.php';
