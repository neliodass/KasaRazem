<?php
require_once "core/Auth.php";
class GroupController extends AppController
{
    private static $controller;
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new GroupController();
        }
        return $instance;
    }
    public function groups()
    {
        Auth::requireLogin();
        $userId = Auth::userId();
//        $groupRepository = GroupRepository::getInstance();
//        $groups = $groupRepository->getGroupsByUserId($userId);
        //$this->render('groups', ['groups' => $groups]);
        $this->render('groups');
    }
}