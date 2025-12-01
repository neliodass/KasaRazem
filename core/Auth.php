<?php

class Auth
{
    public static function requireLogin():void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }
    public static function userId(): ?string
    {
        session_start();
        return $_SESSION['user_id'] ?? null;
    }
}