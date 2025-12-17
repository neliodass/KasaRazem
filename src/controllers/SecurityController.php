<?php


require_once 'AppController.php';
require_once 'repository/UserRepository.php';
require_once 'src/services/UserService.php';
require_once 'src/dtos/CreateUserRequestDTO.php';
require_once 'src/dtos/LoginRequestDTO.php';

class SecurityController extends AppController
{
    private UserService $userService;
    private function __construct()
    {
        $this->userService = UserService::getInstance();
    }
    private static ?self $instance= null;
    public static function getInstance():self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function login()
    {
        if (!$this->isPost()) {
             return $this->render('login');
        }
        try{
            $dto = LoginRequestDTO::fromPost($_POST);
            $this->userService->login($dto);
        } catch (InvalidArgumentException $e) {
             return $this->render('login', ["message" => "Niewłaściwy email, bądź hasło"]);
        }
        header('Location: /groups');
        exit();
    }

    public function register()
    {

        if (!$this->isPost()) {
             return $this->render('register');
        }
        try{
            $dto = CreateUserRequestDTO::fromPost($_POST);
            $this->userService->register($dto);
            return $this->render('login', ["message" => "Rejestracja udana. Proszę się zalogować."]);
        } catch (InvalidArgumentException $e) {
             return $this->render('register', ["message" => $e->getMessage()]);
        }catch (\Exception $e){
                return $this->render('register', ["message" => "Wystąpił błąd podczas rejestracji. Proszę spróbować ponownie."]);
        }

    }
    public function logout()
    {
      $this->userService->logout();
        header('Location: /login');
    }
}