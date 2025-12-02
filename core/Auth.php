<?php

class Auth
{
    public static function requireLogin():void
    {
        self::ensureSessionStarted();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }
    public static function userId(): ?string
    {
        self::ensureSessionStarted();
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
        return isset($_SESSION['user_id']);
    }
}