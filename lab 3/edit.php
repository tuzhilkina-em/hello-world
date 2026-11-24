<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

function editContact(int $id): string
{
    $contacts = array_column(contactList(), null, 'id');
    if (!$contacts) {
        return '<h2>Редактирование записи</h2><p>Нет контактов для редактирования.</p>';
    }
    $row = $contacts[$id] ?? reset($contacts);
    $selected = (int) $row['id'];
    $status = $_SESSION['edited'] ?? null;
    unset($_SESSION['edited']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submitted = contactValues();
        $status = false;
        if ($id === $selected && validToken() && validContact($submitted)) {
            try {
                $query = db()->prepare('UPDATE contacts SET surname = :surname, name = :name,
                    lastname = :lastname, gender = :gender, "date" = :date, phone = :phone,
                    location = :location, email = :email, comment = :comment WHERE id = :id');
                $query->execute($submitted + ['id' => $id]);
                $_SESSION['edited'] = $query->rowCount() > 0;
                redirectTo(['section' => 'edit', 'id' => $id]);
            } catch (PDOException $error) {
                error_log($error->getMessage());
            }
        }
        $row = $submitted;
    }
    ob_start();
    ?>
<h2>Редактирование записи</h2>
<nav class="contact-list" aria-label="Контакты для редактирования">
  <?php foreach ($contacts as $key => $contact): ?>
    <a href="<?= escape(linkTo(['section' => 'edit', 'id' => $key])) ?>"<?= $key === $selected ? ' class="currentRow" aria-current="true"' : '' ?>><?= escape($contact['surname'] . ' ' . $contact['name']) ?></a>
  <?php endforeach ?>
</nav>
<?php if ($status === true): ?><p class="success" role="status">Запись обновлена</p><?php endif ?>
<?php if ($status === false): ?><p class="error" role="alert">Ошибка: запись не обновлена</p><?php endif ?>
<?= contactForm($row, linkTo(['section' => 'edit', 'id' => $selected]), 'Сохранить') ?>
    <?php
    return ob_get_clean();
}
