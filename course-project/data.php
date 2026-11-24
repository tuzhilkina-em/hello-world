<?php
declare(strict_types=1);

function articleDatabase(): PDO
{
    $db = new PDO('sqlite::memory:');
    $db->exec(<<<'SQL'
        PRAGMA foreign_keys = ON;
        CREATE TABLE users (id INTEGER PRIMARY KEY, nickname TEXT NOT NULL UNIQUE);
        CREATE TABLE articles (
            slug TEXT PRIMARY KEY, author_id INTEGER NOT NULL REFERENCES users(id),
            topic TEXT NOT NULL, title TEXT NOT NULL, summary TEXT NOT NULL,
            body TEXT NOT NULL, minutes INTEGER NOT NULL
        );
        INSERT INTO users VALUES (1, 'web_student'), (2, 'polytech_reader');
        INSERT INTO articles VALUES
            ('http', 1, 'HTTP', 'От запроса до ответа',
             'Разбираемся, что браузер отправляет серверу и что получает обратно.',
             'HTTP-запрос содержит метод, адрес и заголовки. GET обычно запрашивает представление ресурса, а POST передаёт данные для обработки. Например, браузер запрашивает список статей методом GET, а форму обратной связи отправляет методом POST.

        Сервер возвращает статус, заголовки и, когда это предусмотрено, тело ответа. Статус 200 означает успешную выдачу страницы, а 404 сообщает, что запрошенный ресурс не найден. После сохранения формы сервер может ответить 303 и направить браузер на страницу результата. Тогда обновление вкладки повторяет GET, а не отправку формы.', 4),
            ('routing', 2, 'PHP', 'Как сервер выбирает маршрут',
             'Путь, HTTP-метод и параметр маршрута на примере страницы статьи.',
             'Маршрутизатор сопоставляет метод и путь запроса с обработчиком. Запрос GET /articles/routing передаёт контроллеру параметр routing. Контроллер загружает статью по этому идентификатору и передаёт данные в шаблон.

        Один URL может иметь несколько обработчиков. Например, GET /feedback показывает форму, а POST /feedback проверяет введённые данные и сохраняет обращение. Разделение упрощает обработку ошибок и проверку поведения приложения.', 5),
            ('sqlite', 1, 'SQL', 'Как связать статью и автора',
             'Первичные и внешние ключи на примере таблиц articles и users.',
             'Поле author_id статьи хранит идентификатор пользователя из таблицы users. У нескольких статей может быть один автор. Внешний ключ помогает базе проверять, что идентификатор указывает на существующего пользователя.

        Подготовленные запросы отделяют текст SQL от передаваемых значений. В контроллере статьи сначала выбирается запись по slug, затем отдельным запросом загружается nickname автора. Перед выводом в HTML данные проходят экранирование.', 6);
        SQL);
    return $db;
}

function feedbackDatabase(): PDO
{
    $dir = getenv('PRACTICE_DATA_DIR');
    if (!$dir) {
        $dir = PHP_SAPI === 'cli-server' ? dirname(__DIR__) . '/course-data' : __DIR__ . '/.course-data';
    }

    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('Не удалось создать каталог базы.');
    }

    $db = new PDO('sqlite:' . $dir . '/feedback.sqlite');
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec("CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY, category TEXT NOT NULL, message TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}
