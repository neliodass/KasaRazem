<?php

require_once "src/services/ListService.php";
require_once "src/services/AuthService.php";
require_once "src/services/GroupService.php";

class ListController extends \AppController
{
    private static $instance = null;
    private ListService $listService;
    private AuthService $authService;
    private GroupService $groupService;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->listService = ListService::getInstance();
        $this->authService = AuthService::getInstance();
        $this->groupService = GroupService::getInstance();
    }

    public function index($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);

        $lists = $this->listService->getListHeaders((int)$groupId);
        $groupName = $this->groupService->getGroupName((int)$groupId);

        $firstListItems = [];
        $activeListId = null;
        if (!empty($lists)) {
            $activeListId = $lists[0]->id;
            $firstListItems = $this->listService->getListItems($activeListId);
        }

        $this->render('shoppingList', [
            'groupId' => $groupId,
            'items' => $firstListItems,
            'activeListId' => $activeListId,
            'lists' => $lists,
            'activeTab' => 'shopping-lists',
            'groupName' => $groupName,
            'inviteId' => $this->groupService->getGroupInviteId((string)$groupId)
        ]);
    }

    public function getListItems($groupId, $listId)
    {
        $this->authService->verifyUserInGroup($groupId);

        $items = $this->listService->getListItems((int)$listId);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    public function toggleItem($groupId, $itemId)
    {
        $itemGroupId = $this->listService->getGroupIdByItemId((int)$itemId);

        if ($itemGroupId !== (int)$groupId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized item access']);
            exit();
        }

        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $dto = ToggleItemRequestDTO::fromJson();
            $success = $this->listService->toggleItemStatus((int)$itemId, $dto);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
    }

    public function deleteItem($groupId, $itemId)
    {
        $itemGroupId = $this->listService->getGroupIdByItemId((int)$itemId);

        if ($itemGroupId !== (int)$groupId) {
            http_response_code(403);
            exit();
        }

        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $success = $this->listService->deleteItem((int)$itemId);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
    }

    public function addList($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $dto = CreateListRequestDTO::fromJson();

            if (!empty($dto->name)) {
                $newId = $this->listService->createList(
                    (int)$groupId,
                    $dto,
                    (int)Auth::userId()
                );

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $newId, 'name' => $dto->name]);
                exit();
            }
        }
    }

    public function deleteList($groupId, $listId)
    {
        $listGroupId = $this->listService->getGroupIdByListId((int)$listId);

        if ($listGroupId !== (int)$groupId) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized list access']);
            exit();
        }

        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $success = $this->listService->deleteList((int)$listId);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }

        header('Content-Type: application/json');
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not supported']);
        exit();
    }

    public function addItem($groupId, $listId)
    {
        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $dto = CreateListItemRequestDTO::fromJson();

            if (!empty($dto->name)) {
                $newId = $this->listService->addItem((int)$listId, $dto);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $newId]);
                exit();
            }
        }
    }

}