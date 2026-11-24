<?php
declare(strict_types=1);

final class BlogController
{
    private const CATEGORIES = ['question' => 'Вопрос', 'idea' => 'Предложение', 'correction' => 'Исправление'];

    public function __construct(private flight\Engine $app, private string $base)
    {
    }

    public function home(): void
    {
        $this->render('home', 'Практикум по веб-разработке', ['articles' => $this->articles()]);
    }

    public function catalog(): void
    {
        $topic = $this->query('topic');
        $topics = ['HTTP', 'PHP', 'SQL'];
        $topic = in_array($topic, $topics, true) ? $topic : '';
        $this->render('catalog', 'Статьи', ['articles' => $this->articles($topic), 'topic' => $topic, 'topics' => $topics]);
    }

    public function article(string $slug): void
    {
        $db = articleDatabase();
        $query = $db->prepare('SELECT * FROM articles WHERE slug = :slug');
        $query->execute(['slug' => $slug]);
        $article = $query->fetch(PDO::FETCH_ASSOC);
        if (!$article) {
            $this->notFound();
            return;
        }

        $author = $db->prepare('SELECT nickname FROM users WHERE id = :id');
        $author->execute(['id' => $article['author_id']]);
        $article['nickname'] = (string) $author->fetchColumn();
        $this->render('article', $article['title'], ['article' => $article]);
    }

    public function planner(): void
    {
        $submitted = isset($_GET['hours']) || isset($_GET['days']);
        $hours = $this->query('hours', '12');
        $days = $this->query('days', '6');
        $validHours = filter_var($hours, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        $validDays = filter_var($days, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 30]]);
        $minutes = $validHours && $validDays ? (int) ceil($validHours * 60 / $validDays) : null;
        $this->render('planner', 'Планировщик', compact('submitted', 'hours', 'days', 'minutes'));
    }

    public function feedback(): void
    {
        $this->startSession();
        $receipt = $_SESSION['receipt'] ?? null;
        unset($_SESSION['receipt']);
        $this->feedbackPage(receipt: $receipt);
    }

    public function submitFeedback(): void
    {
        $this->startSession();
        $category = is_string($_POST['category'] ?? null) ? $_POST['category'] : '';
        $message = is_string($_POST['message'] ?? null) ? trim($_POST['message']) : '';
        $token = $_POST['token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['token'], $token)) {
            $this->app->response()->status(403);
            $this->feedbackPage($category, $message, 'Сессия истекла. Повторите отправку.');
            return;
        }
        if (!isset(self::CATEGORIES[$category]) || mb_strlen($message) < 10 || mb_strlen($message) > 1000) {
            $this->app->response()->status(422);
            $this->feedbackPage($category, $message, 'Выберите тему и введите от 10 до 1000 символов.');
            return;
        }
        if (time() - ($_SESSION['last_submit'] ?? 0) < 3) {
            $this->app->response()->status(429);
            $this->feedbackPage($category, $message, 'Подождите несколько секунд перед повторной отправкой.');
            return;
        }
        try {
            $db = feedbackDatabase();
            $query = $db->prepare('INSERT INTO feedback (category, message) VALUES (:category, :message)');
            $query->execute(compact('category', 'message'));
            $_SESSION['receipt'] = (int) $db->lastInsertId();
            $_SESSION['last_submit'] = time();
        } catch (RuntimeException $exception) {
            error_log($exception->getMessage());
            $this->app->response()->status(503);
            $this->feedbackPage($category, $message, 'Сохранить сообщение сейчас не удалось.');
            return;
        }

        $this->app->redirect('/feedback', 303);
    }

    public function notFound(): void
    {
        $this->app->response()->status(404);
        $this->render('missing', 'Страница не найдена');
    }

    private function articles(string $topic = ''): array
    {
        $db = articleDatabase();
        if ($topic === '') {
            return $db->query('SELECT * FROM articles ORDER BY slug')->fetchAll(PDO::FETCH_ASSOC);
        }

        $query = $db->prepare('SELECT * FROM articles WHERE topic = :topic ORDER BY slug');
        $query->execute(['topic' => $topic]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function feedbackPage(string $category = '', string $message = '', string $error = '', ?int $receipt = null): void
    {
        $categories = self::CATEGORIES;
        $token = $_SESSION['token'];
        $this->app->response()->header('Cache-Control', 'no-store');
        $this->render('feedback', 'Обратная связь', compact('categories', 'category', 'message', 'error', 'receipt', 'token'));
    }

    private function startSession(): void
    {
        session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
        $_SESSION['token'] ??= bin2hex(random_bytes(32));
    }

    private function query(string $key, string $fallback = ''): string
    {
        $value = $_GET[$key] ?? $fallback;
        return is_string($value) ? $value : '';
    }

    private function render(string $page, string $title, array $data = []): void
    {
        $this->app->render('view', $data + [
            'page' => $page, 'title' => $title, 'base' => $this->base,
            'updated' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')))->format('H:i:s'),
        ]);
    }
}
