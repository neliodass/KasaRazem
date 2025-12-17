<?php

class User
{
    public ?int $id = null;
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $password;
    public ?string $profile_picture = null;
    public bool $enabled = true;

    public array $createdGroups = [];
    public array $memberOfGroups = [];

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = isset($data['id']) && $data['id'] !== null && $data['id'] !== '' ? (int)$data['id'] : null;
        $user->firstname = $data['firstname'] ?? '';
        $user->lastname = $data['lastname'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->password = $data['password'] ?? '';
        $user->profile_picture = $data['profile_picture'] ?? null;
        if (isset($data['enabled'])) {
            $user->enabled = filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$data['enabled'];
        } else {
            $user->enabled = true;
        }

        return $user;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'password' => $this->password,
            'profile_picture' => $this->profile_picture,
            'enabled' => (int)$this->enabled,
        ];
    }
}
