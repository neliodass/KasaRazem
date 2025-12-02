<?php
require_once "core/Auth.php";
require_once "repository/GroupRepository.php";
class GroupController extends AppController
{
    private $groupRepository;
    private static $controller;
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new GroupController();
        }
        return $instance;
    }
    private function __construct()
    {
        $this->groupRepository = GroupRepository::getInstance();
    }
    public function groups()
    {
        Auth::requireLogin();
        $userId = Auth::userId();
        if($userId === null){
            header('Location: /login');
            exit();
        }
        $groupRepository = GroupRepository::getInstance();
        $groups = $groupRepository->getGroupsByUserId($userId);
        $this->render('groups', ['groups' => $groups]);
    }
    public function addGroup()
    {
        Auth::requireLogin();
        $this->render('addGroup');
    }
    public function joinGroup()
    {
        Auth::requireLogin();
        $this->render('joinGroup');
    }
}