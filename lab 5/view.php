<?php
if (!isset($escape, $message, $base)) {
    http_response_code(404);
    exit;
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $escape($title ?? 'Мой блог') ?></title>
  <link rel="stylesheet" href="<?= $escape($base . '/style.css') ?>">
</head>
<body>
<header>
  <img src="https://mospolytech.ru/upload/medialibrary/5fa/Logo_Polytech_rus_main.jpg" alt="Московский Политех">
  <h1>Работа с представлением</h1>
</header>
<nav class="menu" aria-label="Навигация">
  <a href="<?= $escape($base . '/index.php') ?>">Главная</a>
  <a href="<?= $escape($base . '/bye/' . rawurlencode('Маша')) ?>">Проверить прощание</a>
  <a href="<?= $escape($base . '/hello/username') ?>">Проверить приветствие</a>
</nav>
<main><h2><?= $escape($message) ?></h2></main>
<footer>задание для самостоятельной работы</footer>
</body>
</html>
