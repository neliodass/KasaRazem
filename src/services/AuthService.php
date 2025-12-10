<?php



class AuthService
{
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private GroupRepository $groupRepository;
    private function __construct()
    {
        $this->groupRepository = GroupRepository::getInstance();
    }
    public function verifyUserInGroup($groupId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }
    }
}