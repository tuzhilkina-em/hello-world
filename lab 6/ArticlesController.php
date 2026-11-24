<?php
declare(strict_types=1);

final class ArticlesController
{
    public function __construct(private PDO $db)
    {
    }

    public function show(int $id): ?array
    {
        $query = $this->db->prepare('SELECT title, body, author_id FROM articles WHERE id = :id');
        $query->execute(['id' => $id]);
        $article = $query->fetch(PDO::FETCH_ASSOC);
        if ($article === false) {
            return null;
        }

        $query = $this->db->prepare('SELECT nickname FROM users WHERE id = :id');
        $query->execute(['id' => $article['author_id']]);
        $nickname = $query->fetchColumn();

        return [
            'title' => $article['title'],
            'body' => $article['body'],
            'nickname' => $nickname === false ? null : $nickname,
        ];
    }
}
