<?php

require_once 'src/validators/PasswordValidator.php';

class CreateUserRequestDTO
{
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $password;

    public static function fromPost(array $postData): self
    {
        if (empty($postData['password']) || $postData['password'] !== ($postData['password-repeat'] ?? '')) {
            throw new InvalidArgumentException("Hasła nie są zgodne lub puste");
        }

        PasswordValidator::validateOrThrow($postData['password']);

        if (!filter_var($postData['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Nieprawidłowy format adresu email.");
        }

        $dto = new self();
        $dto->email = mb_strtolower($postData['email']) ?? '';
        $dto->password = $postData['password'] ?? '';
        $dto->firstname = $postData['firstName'] ?? '';
        $dto->lastname = $postData['lastName'] ?? '';

        return $dto;
    }
}
