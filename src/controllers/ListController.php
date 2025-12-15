<?php

require_once "repository/ListRepository.php";
require_once "src/services/AuthService.php";
require_once "src/services/GroupService.php";
class ListController extends \AppController
{
    private static $instance = null;
    private ListRepository $listRepository;
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
        $this->listRepository = ListRepository::getInstance();
        $this->authService = AuthService::getInstance();
        $this->groupService = GroupService::getInstance();
    }

    public function index($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);
        $lists = $this ->listRepository->getListsHeadersByGroupIdOrderByDate((int)$groupId);
        $groupName = $this->groupService->getGroupName((int)$groupId);

        $firstListItems = [];
        $activeListId = null;
        if (!empty($lists)) {
            $activeListId = $lists[0]['id'];
            $firstListItems = $this->listRepository->getListItems($activeListId);
        }
        $this->render('shoppingList', [
            'groupId' => $groupId,
            'items' => $firstListItems,
            'activeListId' => $activeListId,
            'lists' => $lists,
            'activeTab' => 'shopping-lists',
            'groupName' => $groupName
        ]);

    }
    public function getListItems($groupId, $listId)
    {
        $this->authService->verifyUserInGroup($groupId);

        $items = $this->listRepository->getListItems((int)$listId);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }
    public function toggleItem($groupId, $itemId)
    {
        $itemGroupId = $this->listRepository->getGroupIdByItemId((int)$itemId);

        if ($itemGroupId !== (int)$groupId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized item access']);
            exit();
        }
        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $input = json_decode(file_get_contents('php://input'), true);
            $isPurchased = $input["isPurchased"]??false;

            $success = $this->listRepository->toggleItemStatus((int)$itemId, $isPurchased);
            $item = $this->listRepository->getItemById((int)$itemId);
            $this->listRepository->updateListModificationDate($item['list_id']);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
    }
    public function deleteItem($groupId, $itemId)
    {
        $itemGroupId = $this->listRepository->getGroupIdByItemId((int)$itemId);

        if ($itemGroupId !== (int)$groupId) {
            http_response_code(403);
            exit();
        }
        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $item = $this->listRepository->getItemById((int)$itemId);
            $this->listRepository->updateListModificationDate($item['list_id']);
            $success = $this->listRepository->deleteItem((int)$itemId);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
    }
    public function addList($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);

        if ($this->isPost()) {
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? $_POST['name'] ?? '';

            if (!empty($name)) {
                $newId = $this->listRepository->createList(
                    (int)$groupId,
                    $name,
                    (int)Auth::userId()
                );

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $newId, 'name' => $name]);
                exit();
            }
        }
    }
    public function deleteList($groupId, $listId)
    {
        $listGroupId = $this->listRepository->getGroupIdByListId((int)$listId);

        if ($listGroupId !== (int)$groupId) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized list access']);
            exit();
        }
        $this->authService->verifyUserInGroup($groupId);
        if ($this->isPost()) {
            $success = $this->listRepository->deleteList((int)$listId);

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
            $input = json_decode(file_get_contents('php://input'), true);
             $name = $input['name'] ?? $_POST['name'] ?? '';
            $subtitle = $input['subtitle'] ?? $_POST['subtitle'] ?? '';

            if(!empty($name)){
                $newId = $this->listRepository->addItem((int)$listId, $name, $subtitle);
                $this->listRepository->updateListModificationDate((int)$listId);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $newId]);
                exit();
            }
        }
    }

}