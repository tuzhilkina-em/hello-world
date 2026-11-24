<?php
declare(strict_types=1);

define('CONTACT_BOOK', true);
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['book'] ??= bin2hex(random_bytes(24));

require __DIR__ . '/common.php';
require __DIR__ . '/menu.php';
require __DIR__ . '/viewer.php';
require __DIR__ . '/add.php';
require __DIR__ . '/edit.php';
require __DIR__ . '/delete.php';

function section(): string
{
    $page = $_GET['section'] ?? 'view';
    return in_array($page, ['view', 'add', 'edit', 'delete'], true) ? $page : 'view';
}

function sortMode(): string
{
    $sort = $_GET['sort'] ?? 'added';
    return array_key_exists(is_string($sort) ? $sort : '', SORTS) ? $sort : 'added';
}

try {
    $id = positiveInt($_GET['id'] ?? null, 0);
    $content = match (section()) {
        'add' => addContact(),
        'edit' => editContact($id),
        'delete' => deleteContact($id),
        default => viewContacts(sortMode(), positiveInt($_GET['page'] ?? null)),
    };
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    $content = '<p class="error">Не удалось открыть базу данных.</p>';
}

header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Записная книжка</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <img src="https://mospolytech.ru/upload/medialibrary/5fa/Logo_Polytech_rus_main.jpg" alt="Московский Политех">
  <h1>Записная книжка</h1>
</header>
<?= menu() ?>
<main><?= $content ?></main>
<footer>задание для самостоятельной работы</footer>
</body>
</html>
