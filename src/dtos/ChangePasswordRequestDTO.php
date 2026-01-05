<?php

require_once 'src/validators/PasswordValidator.php';

class ChangePasswordRequestDTO
{
    public string $currentPassword;
    public string $newPassword;
    public string $confirmPassword;

    public static function fromPost(): self
    {
        $dto = new self();
        $dto->currentPassword = $_POST['current_password'] ?? '';
        $dto->newPassword = $_POST['new_password'] ?? '';
        $dto->confirmPassword = $_POST['confirm_password'] ?? '';
        return $dto;
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->currentPassword)) {
            $errors[] = 'Obecne hasło jest wymagane.';
        }

        // Walidacja nowego hasła za pomocą wspólnego walidatora
        $passwordErrors = PasswordValidator::validate($this->newPassword);
        $errors = array_merge($errors, $passwordErrors);

        if (!PasswordValidator::passwordsMatch($this->newPassword, $this->confirmPassword)) {
            $errors[] = 'Nowe hasła nie są identyczne.';
        }

        if (!empty($this->currentPassword) && !empty($this->newPassword)
            && $this->currentPassword === $this->newPassword) {
            $errors[] = 'Nowe hasło musi być inne niż obecne.';
        }

        return $errors;
    }
}
