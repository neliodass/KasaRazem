<?php
require_once "core/Auth.php";
require_once "src/services/GroupService.php";
require_once "src/dtos/CreateGroupRequestDTO.php";
require_once "src/dtos/GroupJoinByCodeRequestDTO.php";
require_once "src/IconsHelper.php";
class GroupController extends AppController
{
    private $groupService;
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    private function __construct()
    {
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


    public function groupDetails($groupId)
    {
        Auth::requireLogin();
        header('Location: /groups/' . $groupId . '/expenses');
        exit();

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

        $code = $inviteCode;
        $message = null;

        if ($this->isPost()) {
            try {
                $dto = GroupJoinByCodeRequestDto::fromPost();
                $code = $dto->code;
                $userId = (int)Auth::userId();

                if ($this->groupService->joinGroup($code, $userId)) {
                    header("Location: /groups");
                    exit;
                }

            } catch (ValidationException $e) {
                $message = $e->getMessage();
                $code = $_POST['code'] ?? $inviteCode;

            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }

        $this->render('joinGroup', ['code' => $code, 'message' => $message]);
    }

    public function createGroup()
    {
        if (!$this->isPost()) {
            $this->render('addGroup');
            return;
        }
        Auth::requireLogin();
        try{
            $dto = CreateGroupRequestDTO::fromPost($_POST);
            if ($this->groupService->createGroup($dto)) {
                header("Location: /groups");
                exit;
            }
        }
        catch (InvalidArgumentException $e) {
            $this->render('createGroup', ['message' => $e->getMessage()]);
            return;
        }
        catch(Exception $e){
            $this->render('createGroup', ['message' => 'Wystąpił nieznany błąd podczas tworzenia grupy.']);
            return;
        }

    }


}