<?php


require_once 'AppController.php';
require_once 'repository/UserRepository.php';
require_once 'src/services/UserService.php';
require_once 'src/dtos/CreateUserRequestDTO.php';
require_once 'src/dtos/LoginRequestDTO.php';
require_once 'src/services/TranslationService.php';

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
            $demoMode = getenv('DEMO_MODE') === 'true';
            return $this->render('login', ['demoMode' => $demoMode]);
        }

        $this->requireCSRF();

        try{
            $dto = LoginRequestDTO::fromPost($_POST);
            $this->userService->login($dto);
        } catch (InvalidArgumentException $e) {
            $demoMode = getenv('DEMO_MODE') === 'true';
            return $this->render('login', ["message" => $e->getMessage(), 'demoMode' => $demoMode]);
        }
        header('Location: /groups');
        exit();
    }

    public function register()
    {

        if (!$this->isPost()) {
             return $this->render('register');
        }

        $this->requireCSRF();

        try{
            $dto = CreateUserRequestDTO::fromPost($_POST);
            $this->userService->register($dto);
            return $this->render('login', ["message" => trans('register.success')]);
        } catch (InvalidArgumentException $e) {
             return $this->render('register', ["message" => $e->getMessage()]);
        }catch (\Exception $e){
                return $this->render('register', ["message" => trans('register.error')]);
        }

    }
    public function logout()
    {
      $this->userService->logout();
        header('Location: /login');
    }

    public function changeLanguage()
    {
        $lang = $_POST['lang'] ?? $_GET['lang'] ?? 'pl';
        TranslationService::getInstance()->setLanguage($lang);

        $redirect = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $redirect");
        exit();
    }
}