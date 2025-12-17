<?php

class UserSelectOutputDTO
{
    public int $id;
    public string $email;
    public string $firstname;
    public string $lastname;
    public ?string $profile_picture;

    public function __construct(User $user)
    {
        $this->id = $user->id;
        $this->email = $user->email;
        $this->firstname = $user->firstname;
        $this->lastname = $user->lastname;
        $this->profile_picture = $user->profile_picture;
    }

    public static function fromUser(User $user): self
    {
        return new self($user);
    }

    public static function fromUsers(array $users): array
    {
        return array_map(fn(User $user) => new self($user), $users);
    }

    public function getFullName(): string
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}

