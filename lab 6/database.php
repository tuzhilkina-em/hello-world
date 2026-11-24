<?php
declare(strict_types=1);

function openDatabase(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec(<<<'SQL'
        PRAGMA foreign_keys = ON;
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            nickname TEXT NOT NULL UNIQUE
        );
        CREATE TABLE articles (
            id INTEGER PRIMARY KEY,
            author_id INTEGER NOT NULL REFERENCES users(id),
            title TEXT NOT NULL,
            body TEXT NOT NULL
        );
        INSERT INTO users (id, nickname) VALUES (1, 'polytech_reader'), (2, 'web_student');
        INSERT INTO articles VALUES
            (1, 1, 'Как работает маршрутизация', 'Маршрут передаёт идентификатор статьи в контроллер.'),
            (2, 2, 'Как устроено представление', 'Шаблон выводит текст статьи и никнейм её автора.');
        SQL);
    return $db;
}
