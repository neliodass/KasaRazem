<?php

class UpdateProfileRequestDTO
{
    public string $firstname;
    public string $lastname;
    public string $email;
    public ?string $profile_picture = null;
    public bool $removeProfilePicture = false;
    public ?array $uploadedFile = null;

    public static function fromPost(): self
    {
        $dto = new self();
        $dto->firstname = trim($_POST['firstname'] ?? '');
        $dto->lastname = trim($_POST['lastname'] ?? '');
        $dto->email = trim($_POST['email'] ?? '');
        $dto->removeProfilePicture = isset($_POST['remove_profile_picture']) && $_POST['remove_profile_picture'] === '1';

        // Obsługa uploadu pliku
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $dto->uploadedFile = $_FILES['profile_picture'];
        }

        return $dto;
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->firstname)) {
            $errors[] = 'Imię jest wymagane.';
        }

        if (empty($this->lastname)) {
            $errors[] = 'Nazwisko jest wymagane.';
        }

        if (empty($this->email)) {
            $errors[] = 'Email jest wymagany.';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Nieprawidłowy format adresu email.';
        }

        // Walidacja uploadu pliku
        if ($this->uploadedFile !== null) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($this->uploadedFile['type'], $allowedTypes)) {
                $errors[] = 'Nieprawidłowy format pliku. Akceptowane formaty: JPG, PNG, WEBP.';
            }

            if ($this->uploadedFile['size'] > $maxSize) {
                $errors[] = 'Plik jest za duży. Maksymalny rozmiar to 5MB.';
            }
        }

        return $errors;
    }
}
