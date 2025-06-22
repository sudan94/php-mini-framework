<?php

namespace App\Core;

use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class Controller
{
    protected Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'cache' => $_ENV['APP_ENV'] === 'production' ? __DIR__ . '/../../cache' : false,
            'debug' => $_ENV['APP_DEBUG'] ?? false,
        ]);

        $this->twig->addGlobal('session', $_SESSION ?? []);

        $this->twig->addFunction(new TwigFunction('flash', function (string $key) {
            return Session::flash($key);
        }));
    }

    protected function render(string $view, array $data = []): string
    {
        return $this->twig->render($view, $data);
    }

    protected function redirect(string $url): void
    {
        header("Location: $url", true, 302);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validateCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            return hash_equals($_SESSION['csrf_token'] ?? '', $token);
        }
        return true;
    }

    protected function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
