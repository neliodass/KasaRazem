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
        return;
    }
    public function joinGroup($inviteCode = null)
    {
        Auth::requireLogin();
        if (!$this->isPost()) {
            $this->render('joinGroup', ['code'=> $inviteCode?? null,'message'=> $message?? null]);
            return;
        }
        $code = $_POST['code'];
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $code)) {
            $message = "Nieprawidłowy kod zaproszenia.";
            $this->render('joinGroup', ['code'=> $code,'message'=> $message]);
            return;
        }
        $groupId = $this->groupRepository->getGroupIdByInviteCode($code);
        if ($groupId === null) {
            $message = "Nieprawidłowy kod zaproszenia.";
            $this->render('joinGroup', ['code'=> $code,'message'=> $message]);
            return;
        }
        if($this->groupRepository->isUserInGroup($groupId, (int)Auth::userId())){
            $message = "Już jesteś członkiem tej grupy.";
            $this->render('joinGroup', ['code'=> $code,'message'=> $message]);
            return;
        }
        if ($this->groupRepository->addUserToGroup($groupId, (int)Auth::userId())) {
            header("Location: /groups");
            exit;
        }
        $this->render('joinGroup', ['message' => 'Wystąpił nieznany błąd podczas dołączania.']);
    }

    public function createGroup()
    {
        Auth::requireLogin();
        if (!$this->isPost()) {
            $this->render('createGroup');
            return;
        }
        $groupName = $_POST['group_name'];
        if (empty($groupName) || strlen($groupName) > 100) {
            $this->render('createGroup', ['message' => 'Nazwa grupy musi mieć od 1 do 100 znaków.']);
            return;
        }
        $newGroupId = $this->groupRepository->createGroup($groupName, (int)Auth::userId());
        if ($newGroupId !== null) {
            header("Location: /groups");
            exit;
        } else {
            $this->render('createGroup', ['message' => 'Wystąpił nieznany błąd podczas tworzenia grupy.']);
            return;
        }
    }
    public function groupDetails($groupId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }
        $group = $this->groupRepository->getGroupById($groupId);
        if ($group === null) {
            header("Location: /groups");
            exit;
        }
        $this->render('groupDetails', ['group' => $group]);
    }
}