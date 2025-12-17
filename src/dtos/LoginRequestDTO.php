<?php


class LoginRequestDTO
{
    public string $email;
    public string $password;
    public bool $rememberMe;


    public static function fromPost(array $postData): self
    {
        $dto = new self();
        $dto->email = mb_strtolower($postData['email']) ?? '';
        $dto->password = $postData['password'] ?? '';
        $dto->rememberMe = isset($postData['remember_me']);

        return $dto;
    }
}