<?php

class RememberMe
{
    private const COOKIE_NAME = 'remember_me';
    private const COOKIE_LIFETIME = 30 * 24 * 60 * 60;

    public static function set(string $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(16));
        
        $hashedToken = hash('sha256', $token);
        
        self::storeToken($userId, $selector, $hashedToken);
        
        $cookieValue = $selector . ':' . $token;
        setcookie(
            self::COOKIE_NAME,
            $cookieValue,
            time() + self::COOKIE_LIFETIME,
            '/',
            '',
            false,
            true
        );
    }

    public static function check(): ?string
    {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return null;
        }

        $cookieValue = $_COOKIE[self::COOKIE_NAME];
        $parts = explode(':', $cookieValue);

        if (count($parts) !== 2) {
            self::clear();
            return null;
        }

        [$selector, $token] = $parts;
        
        $storedData = self::getStoredToken($selector);
        
        if (!$storedData) {
            self::clear();
            return null;
        }

        $hashedToken = hash('sha256', $token);
        
        if (!hash_equals($storedData['token'], $hashedToken)) {
            self::clear();
            return null;
        }

        if (time() > $storedData['expires']) {
            self::deleteToken($selector);
            self::clear();
            return null;
        }

        return $storedData['user_id'];
    }

    public static function clear(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $cookieValue = $_COOKIE[self::COOKIE_NAME];
            $parts = explode(':', $cookieValue);
            
            if (count($parts) === 2) {
                self::deleteToken($parts[0]);
            }
        }

        setcookie(
            self::COOKIE_NAME,
            '',
            time() - 3600,
            '/',
            '',
            false,
            true
        );
    }

    private static function storeToken(string $userId, string $selector, string $hashedToken): void
    {
        $database = new Database();
        $stmt = $database->connect()->prepare(
            'INSERT INTO remember_tokens (user_id, selector, token, expires) 
             VALUES (?, ?, ?, ?) 
             ON CONFLICT (user_id) 
             DO UPDATE SET selector = EXCLUDED.selector, token = EXCLUDED.token, expires = EXCLUDED.expires'
        );
        
        $expires = time() + self::COOKIE_LIFETIME;
        $stmt->execute([$userId, $selector, $hashedToken, $expires]);
    }

    private static function getStoredToken(string $selector): ?array
    {
        $database = new Database();
        $stmt = $database->connect()->prepare(
            'SELECT user_id, token, expires FROM remember_tokens WHERE selector = ?'
        );
        $stmt->execute([$selector]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private static function deleteToken(string $selector): void
    {
        $database = new Database();
        $stmt = $database->connect()->prepare(
            'DELETE FROM remember_tokens WHERE selector = ?'
        );
        $stmt->execute([$selector]);
    }
}

