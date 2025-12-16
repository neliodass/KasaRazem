<?php

require_once 'repository/UserRepository.php';
require_once 'src/entities/User.php';
require_once 'src/dtos/CreateUserRequestDTO.php';
require_once 'src/dtos/LoginRequestDTO.php';

class UserService
{
    private UserRepository $userRepository;
    private static $instance = null;

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

    public function register(CreateUserRequestDTO $dto): User
    {
        if ($this->userRepository->getUserByEmail($dto->email)) {
            throw new InvalidArgumentException("Email jest już w użyciu");
        }
        $hashedPassword = password_hash($dto->password, PASSWORD_BCRYPT);
        $user = new User();
        $user->firstname = $dto->firstname;
        $user->lastname = $dto->lastname;
        $user->email = $dto->email;
        $user->password = $hashedPassword;
        $user->bio = $dto->bio;
        $user->enabled = true;

        return $this->userRepository->save($user);
    }

    public function login(LoginRequestDTO $dto): User
    {
        $user = $this->userRepository->getUserByEmail($dto->email);
        if(!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Niewłaściwy email, bądź hasło");
        }

        if (!$user || !password_verify($dto->password, $user->password)) {
            throw new InvalidArgumentException("Niewłaściwy email, bądź hasło");
        }
        session_start();
        $_SESSION['user_id'] = $user->id;
        return $user;
    }
}