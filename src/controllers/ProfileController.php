<?php

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

}