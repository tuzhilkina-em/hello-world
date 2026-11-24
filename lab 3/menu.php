<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

function menu(): string
{
    $items = ['view' => 'Просмотр', 'add' => 'Добавление записи',
        'edit' => 'Редактирование записи', 'delete' => 'Удаление записи'];
    $active = section();
    ob_start();
    ?>
<nav class="menu" aria-label="Основное меню">
  <?php foreach ($items as $key => $label): ?>
    <a href="<?= escape(linkTo(['section' => $key])) ?>"<?= $key === $active ? ' class="select" aria-current="page"' : '' ?>><?= escape($label) ?></a>
  <?php endforeach ?>
</nav>
<?php if ($active === 'view'): ?>
<nav class="submenu" aria-label="Сортировка">
  <?php foreach (SORTS as $key => [$label]): ?>
    <a href="<?= escape(linkTo(['sort' => $key])) ?>"<?= $key === sortMode() ? ' class="select" aria-current="true"' : '' ?>><?= escape($label) ?></a>
  <?php endforeach ?>
</nav>
<?php endif ?>
    <?php
    return ob_get_clean();
}
