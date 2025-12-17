<?php

class TranslationService
{
    private static ?self $instance = null;
    private array $translations = [];
    private string $currentLang = 'pl';
    private array $supportedLangs = ['pl', 'en'];

    private function __construct()
    {
        $this->detectAndSetLanguage();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function detectAndSetLanguage(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $lang = 'pl';

        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->supportedLangs)) {
            $lang = $_SESSION['lang'];
        } elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $this->supportedLangs)) {
            $lang = $_COOKIE['lang'];
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, $this->supportedLangs)) {
                $lang = $browserLang;
            }
        }

        $this->loadLanguage($lang);
    }

    private function loadLanguage(string $lang): void
    {
        $file = "lang/{$lang}.php";
        if (file_exists($file)) {
            $this->translations = require $file;
            $this->currentLang = $lang;
        }
    }

    public function setLanguage(string $lang): void
    {
        if (!in_array($lang, $this->supportedLangs)) {
            return;
        }

        $this->loadLanguage($lang);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, time() + (365 * 24 * 60 * 60), '/', '', false, true);
    }

    public function get(string $key, array $params = []): string
    {
        $keys = explode('.', $key);
        $value = $this->translations;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $key;
            }
            $value = $value[$k];
        }

        foreach ($params as $param => $val) {
            $value = str_replace(":{$param}", $val, $value);
        }

        return $value;
    }

    public function getCurrentLang(): string
    {
        return $this->currentLang;
    }

    public function getSupportedLangs(): array
    {
        return $this->supportedLangs;
    }
}

function trans(string $key, array $params = []): string
{
    return TranslationService::getInstance()->get($key, $params);
}
