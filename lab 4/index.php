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
} elseif (preg_match('~^/bye/([^/]+)/?$~D', $path, $matches)) {
    $name = trim(rawurldecode($matches[1]));
    if ($name !== '' && strlen($name) <= 240 && !str_contains($name, '/') && preg_match('//u', $name)) {
        $message = (new GreetingController())->sayBye($name);
    }
}
if (!isset($message)) {
    http_response_code(404);
    $message = 'Страница не найдена';
}
$escape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Домашняя работа с роутингом</title>
  <link rel="stylesheet" href="<?= $escape($base . '/style.css') ?>">
</head>
<body>
<header>
  <img src="https://mospolytech.ru/upload/medialibrary/5fa/Logo_Polytech_rus_main.jpg" alt="Московский Политех">
  <h1>Роутинг</h1>
</header>
<nav class="menu" aria-label="Навигация">
  <a href="<?= $escape($base . '/index.php') ?>">Главная</a>
  <a href="<?= $escape($base . '/bye/' . rawurlencode('Маша')) ?>">Проверить маршрут</a>
</nav>
<main><h2><?= $escape($message) ?></h2></main>
<footer>задание для самостоятельной работы</footer>
</body>
</html>
