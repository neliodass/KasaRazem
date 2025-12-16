<?php

class User
{
    public ?int $id = null;
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $password;
    public ?string $bio = null;
    public bool $enabled = true;

    /** @var Group[] */
    public array $createdGroups = [];
    /** @var Group[] */
    public array $memberOfGroups = [];
}