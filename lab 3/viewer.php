<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

function viewContacts(string $sort, int $page): string
{
    $perPage = 10;
    $db = db();
    $total = (int) $db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($pages, $page));
    $order = (SORTS[$sort] ?? SORTS['added'])[1];
    $query = $db->prepare("SELECT * FROM contacts ORDER BY $order LIMIT :limit OFFSET :offset");
    $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $query->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
    $query->execute();
    $rows = $query->fetchAll();
    ob_start();
    ?>
<h2>Просмотр контактов</h2>
<?php if (!$rows): ?>
  <p>В записной книжке пока нет контактов.</p>
<?php else: ?>
<div class="table-scroll">
  <table>
    <thead><tr><?php foreach (FIELDS as $label): ?><th scope="col"><?= escape($label) ?></th><?php endforeach ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr><?php foreach (FIELDS as $key => $label): ?><td><?= escape($row[$key]) ?></td><?php endforeach ?></tr>
    <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php endif ?>
<?php if ($pages > 1): ?>
<nav class="pagination" aria-label="Страницы контактов">
  <?php for ($number = 1; $number <= $pages; $number++): ?>
    <a href="<?= escape(linkTo(['sort' => $sort, 'page' => $number])) ?>"<?= $number === $page ? ' aria-current="page"' : '' ?>><?= $number ?></a>
  <?php endfor ?>
</nav>
<?php endif ?>
    <?php
    return ob_get_clean();
}
