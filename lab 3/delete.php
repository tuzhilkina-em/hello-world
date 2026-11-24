<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

function deleteContact(int $id): string
{
    $surname = $_SESSION['deleted'] ?? null;
    unset($_SESSION['deleted']);
    $error = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($id && validToken()) {
            $query = db()->prepare('DELETE FROM contacts WHERE id = :id RETURNING surname');
            $query->execute(['id' => $id]);
            $deleted = $query->fetchColumn();
            $query->closeCursor();
            if ($deleted !== false) {
                $_SESSION['deleted'] = $deleted;
                redirectTo(['section' => 'delete']);
            }
        }
        $error = true;
    }
    $contacts = contactList();
    ob_start();
    ?>
<h2>Удаление записи</h2>
<?php if ($surname !== null): ?><p class="success" role="status">Запись с фамилией <?= escape($surname) ?> удалена</p><?php endif ?>
<?php if ($error): ?><p class="error" role="alert">Ошибка: запись не удалена</p><?php endif ?>
<?php if (!$contacts): ?><p>Нет контактов для удаления.</p><?php endif ?>
<nav class="contact-list" aria-label="Контакты для удаления">
  <?php foreach ($contacts as $contact): ?>
    <?php $label = $contact['surname'];
    foreach (['name', 'lastname'] as $field) {
        if (preg_match('/^./u', $contact[$field], $initial)) {
            $label .= ' ' . $initial[0] . '.';
        }
    } ?>
    <form id="delete-<?= (int) $contact['id'] ?>" method="post" action="<?= escape(linkTo(['section' => 'delete', 'id' => $contact['id']])) ?>">
      <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
      <a href="<?= escape(linkTo(['section' => 'delete', 'id' => $contact['id']])) ?>" onclick="event.preventDefault(); this.closest('form').requestSubmit();"><?= escape($label) ?></a>
      <?php if ($id === (int) $contact['id']): ?><button type="submit">Удалить</button><?php endif ?>
    </form>
  <?php endforeach ?>
</nav>
    <?php
    return ob_get_clean();
}
