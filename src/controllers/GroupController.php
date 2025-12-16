<?php
require_once "core/Auth.php";
require_once "repository/GroupRepository.php";
require_once "src/services/GroupService.php";
require_once "src/IconsHelper.php";
class GroupController extends AppController
{
    private $groupRepository;

    private $groupService;
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
        $this->groupService = GroupService::getInstance();
    }
    public function groups()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();
        if($userId === null){
            header('Location: /login');
            exit();
        }
        $groups = $this->groupService->getGroupsListDtoForUser($userId);

        $this->render('groups', ['groupsData' => $groups]);
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
        $this->redirect('/groups/' . $groupId . '/expenses');
    }

}