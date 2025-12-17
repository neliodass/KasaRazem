<?php

require_once 'repository/UserRepository.php';
require_once 'src/dtos/UserProfileOutputDTO.php';
require_once 'src/dtos/ChangePasswordRequestDTO.php';

class ProfileService
{
    private static $instance = null;
    private UserRepository $userRepository;

    private function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUserProfile(int $userId): ?UserProfileOutputDTO
    {
        $user = $this->userRepository->getUserById((string)$userId);

        if (!$user) {
            return null;
        }

        return UserProfileOutputDTO::fromUser($user);
    }

    public function changePassword(int $userId, ChangePasswordRequestDTO $dto): bool
    {
        $user = $this->userRepository->getUserById((string)$userId);

        if (!$user) {
            throw new Exception('Użytkownik nie został znaleziony.');
        }

        // Weryfikacja obecnego hasła
        if (!password_verify($dto->currentPassword, $user->password)) {
            throw new Exception('Obecne hasło jest nieprawidłowe.');
        }

        // Walidacja nowego hasła
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }

        // Zmiana hasła
        $user->password = password_hash($dto->newPassword, PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        return true;
    }
}
