<?php
require_once "core/Auth.php";
require_once "src/services/GroupService.php";
require_once "src/services/AuthService.php";
require_once "src/dtos/CreateGroupRequestDTO.php";
require_once "src/dtos/GroupJoinByCodeRequestDTO.php";
require_once "src/IconsHelper.php";

class GroupController extends AppController
{
    private GroupService $groupService;
    private AuthService $authService;

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
        $this->authService = AuthService::getInstance();
    }

    public function groups()
    {
        Auth::requireLogin();
        $userId = (int)Auth::userId();
        if ($userId === null) {
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
        try {
            $dto = CreateGroupRequestDTO::fromPost($_POST);
            if ($this->groupService->createGroup($dto)) {
                header("Location: /groups");
                exit;
            }
        } catch (InvalidArgumentException $e) {
            $this->render('createGroup', ['message' => $e->getMessage()]);
            return;
        } catch (Exception $e) {
            $this->render('createGroup', ['message' => 'Wystąpił nieznany błąd podczas tworzenia grupy.']);
            return;
        }

    }

    public function deleteGroup($groupId)
    {
        Auth::requireLogin();
        if (!$this->isPost()) {
            header("Location: /groups/$groupId/expenses");
            exit();
        }

        $this->authService->verifyUserInGroup($groupId);

        if ($this->groupService->deleteGroup((int)$groupId)) {
            header("Location: /groups");
            exit();
        } else {
            header("Location: /groups/$groupId/expenses");
            exit();
        }
    }

    public function editGroup($groupId)
    {
        Auth::requireLogin();
        $this->authService->verifyUserInGroup($groupId);

        if (!$this->isPost()) {
            $editGroupDTO = $this->groupService->getGroupForEdit((int)$groupId);
            $usersToDelete = $this->groupService->getUsersToDeleteFromGroup((int)$groupId);
            return $this->render('editGroup', ['groupDto' => $editGroupDTO, 'usersToDelete' => $usersToDelete]);
        }

        try {
            $dto = EditGroupNameDTO::fromPost($_POST);
            $this->groupService->editGroupName($dto);
            header("Location: /groups/$groupId/edit");
            exit();
        } catch (Exception $e) {
            $editGroupDTO = $this->groupService->getGroupForEdit((int)$groupId);
            $usersToDelete = $this->groupService->getUsersToDeleteFromGroup((int)$groupId);
            return $this->render('editGroup', [
                'groupDto' => $editGroupDTO,
                'usersToDelete' => $usersToDelete,
                'message' => 'Błąd podczas aktualizacji nazwy grupy: ' . $e->getMessage()
            ]);
        }

    }

    public function deleteUserFromGroup($groupId)
    {
        Auth::requireLogin();
        if (!$this->isPost()) {
            header("Location: /groups/$groupId/edit");
            exit();
        }

        $this->authService->verifyUserInGroup($groupId);

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        try {
            $this->groupService->deleteUserFromGroup($groupId, $userId);
        } catch (Exception $e) {

        }
        header("Location: /groups/$groupId/edit");
        exit();
    }

}