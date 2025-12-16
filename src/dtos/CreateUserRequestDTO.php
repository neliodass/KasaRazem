<?php

class CreateUserRequestDTO
{
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $password;
    public ?string $bio = null;
 }