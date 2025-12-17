<?php

require_once 'core/CSRF.php';

class AppController
{
    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }
    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    protected function validateCSRF(): bool
    {
        if (!$this->isPost()) {
            return true;
        }

        $token = $_POST['csrf_token'] ?? null;
        return CSRF::validateToken($token);
    }

    protected function requireCSRF(): void
    {
        if (!$this->validateCSRF()) {
            http_response_code(403);
            $this->render('login', ['message' => 'Nieprawidłowy token CSRF. Odśwież stronę i spróbuj ponownie.']);
            exit();
        }
    }

    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";

        if (file_exists($templatePath)) {
            extract($variables);

            $csrfToken = CSRF::getToken();
            $csrfField = function() {
                return CSRF::renderTokenField();
            };

            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }
    protected function redirect(string $url)
    {
        header("Location: " . $url);
        exit();
    }
}