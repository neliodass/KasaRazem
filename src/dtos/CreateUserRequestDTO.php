<?php

class CreateUserRequestDTO
{
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $password;
    public ?string $bio = null;

    private const MIN_PASSWORD_LENGTH = 8;
    private const PASSWORD_DIGIT_PATTERN = '/\d/';
    private const PASSWORD_SPECIAL_CHAR_PATTERN = '/[^a-zA-Z0-9\s]/';
    public static function fromPost(array $postData): self
    {
        if (empty($postData['password']) || $postData['password'] !== ($postData['password-repeat'] ?? '')) {
            throw new InvalidArgumentException("Hasła nie są zgodne lub puste");
        }
        self::validatePassword($postData['password']);
        if (!filter_var($postData['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Nieprawidłowy format adresu email.");
        }

        $dto = new self();
        $dto->email = $postData['email'] ?? '';
        $dto->password = $postData['password'] ?? '';
        $dto->firstname = $postData['firstName'] ?? '';
        $dto->lastname = $postData['lastName'] ?? '';
        $dto->bio = $postData['bio'] ?? null;

        return $dto;
    }

    private static function validatePassword(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new InvalidArgumentException(
                "Hasło musi mieć co najmniej " . self::MIN_PASSWORD_LENGTH . " znaków."
            );
        }
        if (!preg_match(self::PASSWORD_DIGIT_PATTERN, $password)) {
            throw new InvalidArgumentException("Hasło musi zawierać co najmniej jedną cyfrę.");
        }

        if (!preg_match(self::PASSWORD_SPECIAL_CHAR_PATTERN, $password)) {
            throw new InvalidArgumentException("Hasło musi zawierać co najmniej jeden znak specjalny.");
        }
    }
 }