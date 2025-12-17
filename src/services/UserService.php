<?php

require_once 'repository/UserRepository.php';
require_once 'src/entities/User.php';
require_once 'src/dtos/CreateUserRequestDTO.php';
require_once 'src/dtos/LoginRequestDTO.php';
require_once 'core/RememberMe.php';
require_once 'src/services/AuditService.php';
require_once 'src/services/TranslationService.php';

class UserService
{
    private UserRepository $userRepository;
    private AuditService $auditService;
    private static $instance = null;

    private function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
        $this->auditService = AuditService::getInstance();
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
            throw new InvalidArgumentException(trans('register.email_in_use'));
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
            $this->auditService->log('login_failed', $dto->email, [
                'reason' => 'invalid_email_format'
            ]);
            throw new InvalidArgumentException(trans('login.invalid_credentials'));
        }

        if (!$user || !password_verify($dto->password, $user->password)) {
            $this->auditService->log('login_failed', $dto->email, [
                'reason' => !$user ? 'user_not_found' : 'invalid_password'
            ]);
            throw new InvalidArgumentException(trans('login.invalid_credentials'));
        }

        $this->auditService->log('login_success', $dto->email, [
            'user_id' => $user->id
        ]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user->id;

        if (isset($dto->rememberMe) && $dto->rememberMe) {
            RememberMe::set($user->id);
        }

        return $user;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        RememberMe::clear();

        session_unset();
        session_destroy();
    }

}