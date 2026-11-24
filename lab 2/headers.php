<?php
require __DIR__ . '/layout.php';

$url = 'https://httpbin.org/get';
$context = stream_context_create([
    'http' => ['method' => 'HEAD', 'timeout' => 5, 'follow_location' => 0],
]);
$headers = @get_headers($url, false, $context);
$result = $headers === false
    ? 'Не удалось получить заголовки. Попробуйте обновить страницу.'
    : implode("\n", $headers);

pageHeader();
?>
<main>
  <h2>HTTP-заголовки</h2>
  <label for="headers">Ответ <?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?></label>
  <textarea id="headers" class="headers" rows="14" readonly spellcheck="false"><?php echo htmlspecialchars($result, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
  <a class="button secondary" href="index.php">Вернуться к форме</a>
</main>
<?php pageFooter(); ?>
