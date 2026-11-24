<?php
require __DIR__ . '/layout.php';
pageHeader();
?>
<main>
  <h2>Обратная связь</h2>
  <form action="https://httpbin.org/post" method="post">
    <label>Имя пользователя
      <input name="name" autocomplete="name" required>
    </label>
    <label>E-mail пользователя
      <input type="email" name="email" autocomplete="email" required>
    </label>
    <label>Тип обращения
      <select name="feedback_type" required>
        <option value="">Выберите тип</option>
        <option value="complaint">Жалоба</option>
        <option value="suggestion">Предложение</option>
        <option value="thanks">Благодарность</option>
      </select>
    </label>
    <label>Текст обращения
      <textarea name="message" rows="5" required></textarea>
    </label>
    <fieldset>
      <legend>Вариант ответа</legend>
      <label><input type="checkbox" name="reply[]" value="sms"> СМС</label>
      <label><input type="checkbox" name="reply[]" value="email"> E-mail</label>
    </fieldset>
    <div class="actions">
      <button type="submit">Отправить</button>
      <a class="button secondary" href="headers.php">Посмотреть HTTP-заголовки</a>
    </div>
  </form>
</main>
<?php pageFooter(); ?>
