<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

function addContact(): string
{
    $row = array_fill_keys(array_keys(FIELDS), '');
    $success = !empty($_SESSION['added']);
    unset($_SESSION['added']);
    $error = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $row = contactValues();
        if (validToken() && validContact($row)) {
            try {
                $query = db()->prepare('INSERT INTO contacts
                    (surname, name, lastname, gender, "date", phone, location, email, comment)
                    VALUES (:surname, :name, :lastname, :gender, :date, :phone, :location, :email, :comment)');
                $query->execute($row);
                $_SESSION['added'] = true;
                redirectTo(['section' => 'add']);
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
            }
        }
        $error = true;
    }
    ob_start();
    ?>
<h2>Добавление записи</h2>
<?php if ($success): ?><p class="success" role="status">Запись добавлена</p><?php endif ?>
<?php if ($error): ?><p class="error" role="alert">Ошибка: запись не добавлена</p><?php endif ?>
<?= contactForm($row, linkTo(['section' => 'add']), 'Добавить') ?>
    <?php
    return ob_get_clean();
}

function contactForm(array $row, string $action, string $button): string
{
    ob_start();
    ?>
<form method="post" action="<?= escape($action) ?>" class="column">
  <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
  <label class="add">Фамилия <input name="surname" value="<?= escape($row['surname']) ?>" required></label>
  <label class="add">Имя <input name="name" value="<?= escape($row['name']) ?>" required></label>
  <label class="add">Отчество <input name="lastname" value="<?= escape($row['lastname']) ?>"></label>
  <label class="add">Пол
    <select name="gender">
      <option value="">Выберите пол</option>
      <option<?= $row['gender'] === 'мужской' ? ' selected' : '' ?>>мужской</option>
      <option<?= $row['gender'] === 'женский' ? ' selected' : '' ?>>женский</option>
    </select>
  </label>
  <label class="add">Дата рождения <input name="date" type="date" value="<?= escape($row['date']) ?>"></label>
  <label class="add">Телефон <input name="phone" type="tel" value="<?= escape($row['phone']) ?>"></label>
  <label class="add">Адрес <input name="location" value="<?= escape($row['location']) ?>"></label>
  <label class="add">E-mail <input name="email" type="email" value="<?= escape($row['email']) ?>"></label>
  <label class="add">Комментарий <textarea name="comment" rows="3"><?= escape($row['comment']) ?></textarea></label>
  <button class="form-btn" type="submit"><?= escape($button) ?></button>
</form>
    <?php
    return ob_get_clean();
}
