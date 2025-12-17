<?php

class PasswordValidator
{
    private const int MIN_PASSWORD_LENGTH = 8;
    private const string PASSWORD_DIGIT_PATTERN = '/\d/';
    private const string PASSWORD_SPECIAL_CHAR_PATTERN = '/[^a-zA-Z0-9\s]/';

    public static function validate(string $password): array
    {
        $errors = [];

        if (empty($password)) {
            $errors[] = 'Hasło jest wymagane.';
            return $errors;
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors[] = 'Hasło musi mieć co najmniej ' . self::MIN_PASSWORD_LENGTH . ' znaków.';
        }

        if (!preg_match(self::PASSWORD_DIGIT_PATTERN, $password)) {
            $errors[] = 'Hasło musi zawierać co najmniej jedną cyfrę.';
        }

        if (!preg_match(self::PASSWORD_SPECIAL_CHAR_PATTERN, $password)) {
            $errors[] = 'Hasło musi zawierać co najmniej jeden znak specjalny.';
        }

        return $errors;
    }

    public static function validateOrThrow(string $password): void
    {
        $errors = self::validate($password);
        
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }
    }

    public static function passwordsMatch(string $password, string $confirmPassword): bool
    {
        return $password === $confirmPassword;
    }
}

