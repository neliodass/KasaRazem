<?php

require_once 'repository/UserRepository.php';
require_once 'src/dtos/UserProfileOutputDTO.php';
require_once 'src/dtos/ChangePasswordRequestDTO.php';
require_once 'src/dtos/UpdateProfileRequestDTO.php';

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

    public function updateProfile(int $userId, UpdateProfileRequestDTO $dto): bool
    {
        $user = $this->userRepository->getUserById((string)$userId);

        if (!$user) {
            throw new Exception('Użytkownik nie został znaleziony.');
        }

        // Walidacja danych
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }

        // Sprawdzenie czy email nie jest już używany przez innego użytkownika
        $existingUser = $this->userRepository->getUserByEmail($dto->email);
        if ($existingUser && $existingUser->id !== $userId) {
            throw new Exception('Ten adres email jest już używany przez inne konto.');
        }

        // Aktualizacja danych użytkownika
        $user->firstname = $dto->firstname;
        $user->lastname = $dto->lastname;
        $user->email = $dto->email;

        // Obsługa usunięcia zdjęcia profilowego
        if ($dto->removeProfilePicture) {
            // Usuń stary plik jeśli istnieje
            $oldPath = $_SERVER['DOCUMENT_ROOT'] . $user->profile_picture;
            if ($user->profile_picture && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $user->profile_picture = null;
        }

        // Obsługa uploadu nowego zdjęcia
        if ($dto->uploadedFile !== null) {
            // Usuń stare zdjęcie jeśli istnieje
            if ($user->profile_picture) {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . $user->profile_picture;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Utwórz katalog jeśli nie istnieje
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                chmod($uploadDir, 0777);
            }

            // Walidacja i sanityzacja rozszerzenia pliku
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $originalExtension = strtolower(pathinfo($dto->uploadedFile['name'], PATHINFO_EXTENSION));

            // Mapowanie MIME type na rozszerzenie (dla bezpieczeństwa)
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            $extension = $mimeToExt[$dto->uploadedFile['type']] ?? $originalExtension;

            if (!in_array($extension, $allowedExtensions)) {
                throw new Exception('Nieprawidłowe rozszerzenie pliku.');
            }

            // Generuj bezpieczną unikalną nazwę pliku (bez oryginalnej nazwy)
            $filename = sprintf('user_%d_%s.%s', $userId, uniqid(), $extension);
            $uploadPath = $uploadDir . $filename;

            // Przenieś plik
            if (move_uploaded_file($dto->uploadedFile['tmp_name'], $uploadPath)) {
                chmod($uploadPath, 0644);
                $user->profile_picture = '/public/uploads/avatars/' . $filename;
            } else {
                throw new Exception('Nie udało się przesłać pliku. Sprawdź uprawnienia katalogu.');
            }
        }

        $this->userRepository->save($user);

        return true;
    }

    public function changeTheme(int $userId, string $theme): bool
    {
        $user = $this->userRepository->getUserById((string)$userId);

        if (!$user) {
            throw new Exception('Użytkownik nie został znaleziony.');
        }

        if (!in_array($theme, ['light', 'dark'])) {
            throw new Exception('Nieprawidłowy motyw. Dozwolone: light, dark.');
        }

        $user->theme = $theme;
        $this->userRepository->save($user);

        return true;
    }
}
