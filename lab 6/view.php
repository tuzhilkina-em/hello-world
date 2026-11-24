<?php
if (!isset($escape, $title, $home, $base)) {
    http_response_code(404);
    exit;
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $escape($title) ?></title>
  <link rel="stylesheet" href="<?= $escape($base . '/style.css') ?>">
</head>
<body>
<header>
  <img src="https://mospolytech.ru/upload/medialibrary/5fa/Logo_Polytech_rus_main.jpg" alt="Московский Политех">
  <h1>Мой блог</h1>
</header>
<nav class="menu" aria-label="Навигация">
  <a href="<?= $escape($base . '/index.php') ?>">Главная</a>
  <a href="<?= $escape($base . '/articles/1') ?>">Статья о маршрутизации</a>
  <a href="<?= $escape($base . '/articles/2') ?>">Статья о представлении</a>
</nav>
<main>
<?php if ($home): ?>
  <h2>Учебные статьи</h2>
<?php elseif ($article !== null): ?>
  <article>
    <h2><?= $escape($article['title']) ?></h2>
    <p>Автор: <strong><?= $escape($article['nickname'] ?? 'Не указан') ?></strong></p>
    <p><?= $escape($article['body']) ?></p>
  </article>
<?php else: ?>
  <h2>Статья не найдена</h2>
<?php endif ?>
</main>
<footer>задание для самостоятельной работы</footer>
</body>
</html>
