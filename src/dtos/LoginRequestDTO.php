<?php


class LoginRequestDTO
{
    public string $email;
    public string $password;


    public static function fromPost(array $postData): self
    {
        $dto = new self();
        $dto->email = mb_strtolower($postData['email']) ?? '';
        $dto->password = $postData['password'] ?? '';

        return $dto;
    }
}