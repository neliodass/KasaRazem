<?php

require_once 'AppController.php';
class SecurityController extends AppController
{
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
        $password = $_POST['password'];
        //TODO:authenticate user
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

//        if ($password != $confirmPassword) {
//            return $this->render('register', ["message" => "Passwords do not match"]);
//        }
//        if ($this->userRepository->getUserByEmail($email)) {
//            return $this->render('register', ["message" => "Email already in use"]);
//        }
//        $this->userRepository->createUser(
//            $email,
//            password_hash($password, PASSWORD_BCRYPT),
//            $firstName,
//            $lastName
//        );
//        return $this->render('login', ["message" => "Registration successful. Please log in."]);
    }
}