<?php
defined('CONTACT_BOOK') || exit(http_response_code(404));

const FIELDS = [
    'surname' => 'Фамилия', 'name' => 'Имя', 'lastname' => 'Отчество',
    'gender' => 'Пол', 'date' => 'Дата рождения', 'phone' => 'Телефон',
    'location' => 'Адрес', 'email' => 'E-mail', 'comment' => 'Комментарий',
];

const SORTS = [
    'added' => ['В порядке добавления', 'id ASC'],
    'surname' => ['По фамилии', 'surname COLLATE PERSON ASC, name COLLATE PERSON ASC, id ASC'],
    'date' => ['По дате рождения', '("date" = \'\') ASC, "date" ASC, id ASC'],
];

function db(): PDO
{
    static $db;
    if ($db instanceof PDO) {
        return $db;
    }
    $folder = getenv('CONTACT_BOOK_DATA_DIR');
    if (!$folder) {
        $folder = PHP_SAPI === 'cli-server'
            ? dirname(__DIR__) . '/contact-book-data'
            : __DIR__ . '/.contact-book-data';
    }
    if (!is_dir($folder) && !@mkdir($folder, 0700, true) && !is_dir($folder)) {
        throw new PDOException('Cannot create contact book data directory.');
    }
    if ($folder === __DIR__ . '/.contact-book-data') {
        $rules = "Require all denied\n";
        $accessFile = $folder . '/.htaccess';
        if (!is_file($accessFile) && @file_put_contents($accessFile, $rules, LOCK_EX) === false) {
            throw new PDOException('Cannot restrict access to the contact book data directory.');
        }
    }
    $db = new PDO('sqlite:' . $folder . '/' . $_SESSION['book'] . '.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->sqliteCreateCollation('PERSON', static function (string $left, string $right): int {
        $alphabet = preg_split('//u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя', -1, PREG_SPLIT_NO_EMPTY);
        $weights = [];
        foreach ($alphabet as $i => $letter) {
            $weights[$letter] = chr(128 + $i % 33);
        }
        return strcmp(strtr(strtolower($left), $weights), strtr(strtolower($right), $weights));
    });
    $db->exec('CREATE TABLE IF NOT EXISTS contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT, surname TEXT NOT NULL, name TEXT NOT NULL,
        lastname TEXT NOT NULL, gender TEXT NOT NULL, "date" TEXT NOT NULL,
        phone TEXT NOT NULL, location TEXT NOT NULL, email TEXT NOT NULL, comment TEXT NOT NULL
    )');
    return $db;
}

function escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function linkTo(array $query = []): string
{
    return 'index.php' . ($query ? '?' . http_build_query($query) : '');
}

function positiveInt(mixed $value, int $fallback = 1): int
{
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: $fallback;
}

function contactValues(): array
{
    $values = [];
    foreach (FIELDS as $key => $label) {
        $value = $_POST[$key] ?? '';
        $values[$key] = is_string($value) ? trim($value) : '';
    }
    return $values;
}

function validContact(array $values): bool
{
    if ($values['surname'] === '' || $values['name'] === ''
        || !in_array($values['gender'], ['', 'мужской', 'женский'], true)
        || ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL))) {
        return false;
    }
    foreach ($values as $value) {
        if (strlen($value) > 4000) {
            return false;
        }
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['date']);
    return $values['date'] === '' || ($date && $date->format('Y-m-d') === $values['date']);
}

function validToken(): bool
{
    return is_string($_POST['csrf'] ?? null) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function redirectTo(array $query): never
{
    header('Location: ' . linkTo($query), true, 303);
    exit;
}

function contactList(): array
{
    return db()->query('SELECT * FROM contacts ORDER BY ' . SORTS['surname'][1])->fetchAll();
}
