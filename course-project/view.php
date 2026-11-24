<?php
if (!isset($page, $base)) {
    http_response_code(404);
    exit;
}
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$url = static fn (string $path): string => $e($base . $path);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($title ?? 'Веб-практикум') ?> · Веб-практикум</title>
  <link rel="stylesheet" href="<?= $url('/style.css') ?>">
</head>
<body>
<header class="top"><div class="wrap navbar">
  <a class="brand" href="<?= $url('/') ?>"><span class="symbol" aria-hidden="true">&lt;/&gt;</span> Веб-практикум</a>
  <nav aria-label="Основное меню">
    <?php foreach (['catalog' => ['/articles', 'Статьи'], 'planner' => ['/planner', 'Планировщик'], 'feedback' => ['/feedback', 'Обратная связь']] as $key => [$path, $label]): ?>
      <a href="<?= $url($path) ?>"<?= $page === $key || ($page === 'article' && $key === 'catalog') ? ' aria-current="page"' : '' ?>><?= $label ?></a>
    <?php endforeach ?>
  </nav>
</div></header>
<main class="wrap">
<?php if ($page === 'home'): ?>
  <section class="hero">
    <h1>Практикум по<br>веб-разработке</h1>
    <p>Короткие статьи о HTTP, PHP и базах данных. Составь план занятий и отправь вопрос по материалам.</p>
    <div class="actions"><a class="button" href="<?= $url('/articles') ?>">Читать статьи</a><a class="button ghost" href="<?= $url('/planner') ?>">Составить план</a></div>
  </section>
  <h2>Материалы практикума</h2>
<?php elseif ($page === 'catalog'): ?>
  <h1>Статьи</h1>
  <nav class="filters" aria-label="Темы статей">
    <?php foreach (['', ...$topics] as $item): ?>
      <a href="<?= $url('/articles' . ($item !== '' ? '?topic=' . rawurlencode($item) : '')) ?>"<?= $item === $topic ? ' aria-current="true"' : '' ?>><?= $e($item ?: 'Все темы') ?></a>
    <?php endforeach ?>
  </nav>
<?php endif ?>
<?php if ($page === 'home' || $page === 'catalog'): ?>
  <div class="cards">
    <?php foreach ($articles as $item): ?>
      <article class="card">
        <p class="meta"><?= $e($item['topic']) ?> · <?= (int) $item['minutes'] ?> мин</p>
        <h3><a href="<?= $url('/articles/' . $item['slug']) ?>"><?= $e($item['title']) ?></a></h3>
        <p><?= $e($item['summary']) ?></p>
      </article>
    <?php endforeach ?>
  </div>
<?php elseif ($page === 'article'): ?>
  <article class="reading">
    <a href="<?= $url('/articles') ?>">К списку статей</a>
    <h1><?= $e($article['title']) ?></h1>
    <p class="meta"><?= $e($article['topic']) ?> · <?= (int) $article['minutes'] ?> мин · Автор <?= $e($article['nickname']) ?></p>
    <?php foreach (explode("\n\n", $article['body']) as $paragraph): ?><p><?= $e(trim($paragraph)) ?></p><?php endforeach ?>
  </article>
<?php elseif ($page === 'planner'): ?>
  <div class="reading">
    <h1>Планировщик занятий</h1>
    <p>Укажи объём занятий и число дней. Сервер рассчитает нагрузку на один день.</p>
    <form action="<?= $url('/planner') ?>" method="get" class="panel">
      <label>Всего часов <input type="number" name="hours" min="1" max="100" required value="<?= $e($hours) ?>"></label>
      <label>Дней на занятия <input type="number" name="days" min="1" max="30" required value="<?= $e($days) ?>"></label>
      <button>Рассчитать</button>
    </form>
    <?php if ($submitted && $minutes !== null): ?>
      <p class="notice" role="status"><strong><?= $minutes ?> мин в день</strong><br><?= $e($hours) ?> ч на <?= $e($days) ?> дн. Значение округлено вверх до целой минуты.</p>
    <?php elseif ($submitted): ?>
      <p class="notice error" role="alert">Укажи от 1 до 100 часов и от 1 до 30 дней.</p>
    <?php endif ?>
  </div>
<?php elseif ($page === 'feedback'): ?>
  <div class="reading">
    <h1>Обратная связь</h1>
    <p>Отправь вопрос о статье, предложение или исправление.</p>
    <?php if ($receipt !== null): ?><p class="notice" role="status">Обращение № <?= $receipt ?> сохранено.</p><?php endif ?>
    <?php if ($error !== ''): ?><p class="notice error" role="alert"><?= $e($error) ?></p><?php endif ?>
    <form action="<?= $url('/feedback') ?>" method="post" class="panel">
      <input type="hidden" name="token" value="<?= $e($token) ?>">
      <label>Тема обращения <select name="category" required>
        <option value="">Выбрать тему</option>
        <?php foreach ($categories as $key => $label): ?><option value="<?= $e($key) ?>"<?= $key === $category ? ' selected' : '' ?>><?= $e($label) ?></option><?php endforeach ?>
      </select></label>
      <label>Сообщение <textarea name="message" rows="5" minlength="10" maxlength="1000" required><?= $e($message) ?></textarea></label>
      <button>Отправить</button>
    </form>
  </div>
<?php else: ?>
  <div class="reading"><h1>Страница не найдена</h1><p><a href="<?= $url('/articles') ?>">Открыть список статей</a></p></div>
<?php endif ?>
</main>
<footer><div class="wrap footer"><span>Веб-практикум · Учебный проект</span><span>Время ответа сервера <?= $e($updated) ?> МСК</span></div></footer>
</body>
</html>
