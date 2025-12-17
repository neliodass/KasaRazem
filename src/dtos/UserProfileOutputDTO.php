<?php

class UserProfileOutputDTO
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public string $email;
    public ?string $profile_picture;

    public function __construct(User $user)
    {
        $this->id = $user->id;
        $this->firstname = $user->firstname;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->profile_picture = $user->profile_picture;
    }

    public static function fromUser(User $user): self
    {
        return new self($user);
    }

    public function getFullName(): string
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}
