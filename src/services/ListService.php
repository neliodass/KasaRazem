<?php

require_once 'repository/ListRepository.php';
require_once 'src/dtos/ShoppingListHeaderDTO.php';
require_once 'src/dtos/ListItemOutputDTO.php';
require_once 'src/dtos/CreateListRequestDTO.php';
require_once 'src/dtos/CreateListItemRequestDTO.php';
require_once 'src/dtos/ToggleItemRequestDTO.php';

class ListService
{
    private static $instance = null;
    private ListRepository $listRepository;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->listRepository = ListRepository::getInstance();
    }

    /** @return ShoppingListHeaderDTO[] */
    public function getListHeaders(int $groupId): array
    {
        $lists = $this->listRepository->getListsByGroupId($groupId);

        $headers = [];
        foreach ($lists as $list) {
            $headers[] = new ShoppingListHeaderDTO($list);
        }

        return $headers;
    }

    /** @return ListItemOutputDTO[] */
    public function getListItems(int $listId): array
    {
        $items = $this->listRepository->getItemsByListId($listId);

        $itemDtos = [];
        foreach ($items as $item) {
            $itemDtos[] = new ListItemOutputDTO($item);
        }

        return $itemDtos;
    }

    public function createList(int $groupId, CreateListRequestDTO $dto, int $creatorId): ?int
    {
        return $this->listRepository->createList($groupId, $dto->name, $creatorId);
    }

    public function deleteList(int $listId): bool
    {
        return $this->listRepository->deleteList($listId);
    }

    public function addItem(int $listId, CreateListItemRequestDTO $dto): ?int
    {
        $itemId = $this->listRepository->addItem(
            $listId,
            $dto->name,
            $dto->subtitle,
            $dto->quantity,
            $dto->unit
        );

        if ($itemId) {
            $this->listRepository->updateListModificationDate($listId);
        }

        return $itemId;
    }

    public function toggleItemStatus(int $itemId, ToggleItemRequestDTO $dto): bool
    {
        $item = $this->listRepository->getItemById($itemId);

        if (!$item) {
            return false;
        }

        $success = $this->listRepository->toggleItemStatus($itemId, $dto->isPurchased);

        if ($success) {
            $this->listRepository->updateListModificationDate($item->list_id);
        }

        return $success;
    }

    public function deleteItem(int $itemId): bool
    {
        $item = $this->listRepository->getItemById($itemId);

        if (!$item) {
            return false;
        }

        $success = $this->listRepository->deleteItem($itemId);

        if ($success) {
            $this->listRepository->updateListModificationDate($item->list_id);
        }

        return $success;
    }

    public function getGroupIdByItemId(int $itemId): ?int
    {
        return $this->listRepository->getGroupIdByItemId($itemId);
    }

    public function getGroupIdByListId(int $listId): ?int
    {
        return $this->listRepository->getGroupIdByListId($listId);
    }
}

