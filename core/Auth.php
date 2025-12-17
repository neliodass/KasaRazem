<?php

require_once 'core/RememberMe.php';

class Auth
{
    public static function requireLogin():void
    {
        self::ensureSessionStarted();

        if (!isset($_SESSION['user_id'])) {
            $userId = RememberMe::check();

            if ($userId) {
                $_SESSION['user_id'] = $userId;
            } else {
                header('Location: /login');
                exit();
            }
        }
    }

    public static function userId(): ?string
    {
        self::ensureSessionStarted();

        if (!isset($_SESSION['user_id'])) {
            $userId = RememberMe::check();
            if ($userId) {
                $_SESSION['user_id'] = $userId;
            }
        }

        return $_SESSION['user_id'] ?? null;
    }

    private static function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        self::ensureSessionStarted();

        if (isset($_SESSION['user_id'])) {
            return true;
        }

        $userId = RememberMe::check();
        if ($userId) {
            $_SESSION['user_id'] = $userId;
            return true;
        }

        return false;
    }
}