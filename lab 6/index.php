<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
require __DIR__ . '/ArticlesController.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
if (is_string($path) && $base !== '' && str_starts_with($path, $base . '/')) {
    $path = substr($path, strlen($base));
}

$article = null;
$title = 'Мой блог';
$home = in_array($path, ['/', '/index.php'], true);
if (!$home) {
    if (is_string($path) && preg_match('~^/articles/([1-9][0-9]*)/?$~D', $path, $matches)) {
        $id = filter_var($matches[1], FILTER_VALIDATE_INT);
        if ($id !== false) {
            $article = (new ArticlesController(openDatabase()))->show($id);
        }
    }

    if ($article === null) {
        http_response_code(404);
        $title = 'Статья не найдена';
    } else {
        $title = $article['title'];
    }
}

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
require __DIR__ . '/view.php';
