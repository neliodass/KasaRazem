<?php

require_once 'AppController.php';
require_once 'repository/UserRepository.php';
class SecurityController extends AppController
{
    private $userRepository;
    private function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
    }
    private static $instance;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SecurityController();
        }
        return self::$instance;
    }
    public function login()
    {
        if (!$this->isPost()) {
            return $this->render('login');
        }
        $email = $_POST['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('login', ["message" => "Niewłaściwy email, bądź hasło"]);
        }
        $password = $_POST['password'];
        $user = $this->userRepository->getUserByEmail($email);
        if (!$user || !password_verify($password, $user->password)) {
            return $this->render('login', ["message" => "Niewłaściwy email, bądź hasło"]);
        }
        session_start();
        $_SESSION['user_id'] = $user->id;
        header('Location: /groups');
        exit();
    }

    public function register()
    {

        if (!$this->isPost()) {
            return $this->render('register');
        }
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password-repeat'] ?? '';
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';

        if ($password != $confirmPassword) {
            return $this->render('register', ["message" => "Passwords do not match"]);
        }
        if ($this->userRepository->getUserByEmail($email)) {
            return $this->render('register', ["message" => "Email already in use"]);
        }
        $this->userRepository->createUser(
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $firstName,
            $lastName
        );
        return $this->render('login', ["message" => "Registration successful. Please log in."]);
    }
    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: /login");
    }
}