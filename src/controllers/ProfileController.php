<?php

require_once 'config.php';
require_once 'src/services/ProfileService.php';

class ProfileController extends \AppController
{
    private static $instance = null;
    private ProfileService $profileService;

    private function __construct()
    {
        $this->profileService = ProfileService::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getProfile()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();

        $user = $this->profileService->getUserProfile($userId);

        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->render('profile', ['user' => $user]);
    }

    public function changePassword()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();

        if (!$this->isPost()) {
            $this->render('changePassword');
            return;
        }

        try {
            $dto = ChangePasswordRequestDTO::fromPost();
            $this->profileService->changePassword($userId, $dto);

            $this->render('changePassword', [
                'message' => 'Hasło zostało pomyślnie zmienione.',
                'success' => true
            ]);
        } catch (Exception $e) {
            $this->render('changePassword', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editProfile()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();

        if (!$this->isPost()) {
            $user = $this->profileService->getUserProfile($userId);
            
            if (!$user) {
                $this->redirect('/login');
                return;
            }
            
            $this->render('editProfile', ['user' => $user]);
            return;
        }

        try {
            $dto = UpdateProfileRequestDTO::fromPost();
            $this->profileService->updateProfile($userId, $dto);
            
            $this->redirect('/profile');
        } catch (Exception $e) {
            $user = $this->profileService->getUserProfile($userId);
            $this->render('editProfile', [
                'user' => $user,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function changeTheme()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $theme = $data['theme'] ?? '';

            $this->profileService->changeTheme($userId, $theme);

            echo json_encode(['success' => true, 'theme' => $theme]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}