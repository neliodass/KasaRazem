<?php

require_once 'repository/UserRepository.php';
require_once 'src/dtos/UserProfileOutputDTO.php';

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
}
