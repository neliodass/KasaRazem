<?php

require_once "Repository.php";
require_once "repository/UserRepository.php";
require_once "src/entities/ShoppingList.php";
require_once "src/entities/ListItem.php";
require_once "src/entities/User.php";

class ListRepository extends Repository
{
    private static $instance;
    private UserRepository $userRepository;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    private function hydrateShoppingList(array $data): ShoppingList
    {
        $list = new ShoppingList();
        $list->id = (int)$data['id'];
        $list->group_id = (int)$data['group_id'];
        $list->name = $data['name'];
        $list->created_by_user_id = (int)$data['created_by_user_id'];
        $list->created_at = new DateTimeImmutable($data['created_at']);
        $list->updated_at = new DateTimeImmutable($data['updated_at']);

        if (isset($data['created_by_user_id'])) {
            $list->createdBy = $this->userRepository->getUserById((string)$data['created_by_user_id']);
        }

        return $list;
    }

    private function hydrateListItem(array $data): ListItem
    {
        $item = new ListItem();
        $item->id = (int)$data['id'];
        $item->list_id = (int)$data['list_id'];
        $item->name = $data['name'];
        $item->subtitle = $data['subtitle'] ?? null;
        $item->quantity = (float)$data['quantity'];
        $item->unit = $data['unit'];
        $item->is_in_cart = (bool)$data['is_in_cart'];
        $item->is_purchased = (bool)$data['is_purchased'];
        $item->purchased_by_user_id = isset($data['purchased_by_user_id']) ? (int)$data['purchased_by_user_id'] : null;

        if ($item->purchased_by_user_id) {
            $item->purchasedBy = $this->userRepository->getUserById((string)$item->purchased_by_user_id);
        }

        return $item;
    }

    public function getListsByGroupId(int $groupId): array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM shopping_lists WHERE group_id = :groupId ORDER BY updated_at DESC'
        );

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->execute();

        $listsData = $query->fetchAll(PDO::FETCH_ASSOC);

        $lists = [];
        foreach ($listsData as $data) {
            $lists[] = $this->hydrateShoppingList($data);
        }

        return $lists;
    }

    public function getListById(int $listId): ?ShoppingList
    {
        $query = $this->conn->prepare(
            'SELECT * FROM shopping_lists WHERE id = :listId'
        );
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $list = $this->hydrateShoppingList($data);
        $list->items = $this->getItemsByListId($listId);

        return $list;
    }

    public function getItemsByListId(int $listId): array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM list_items 
             WHERE list_id = :listId 
             ORDER BY is_purchased ASC, id DESC'
        );
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        $query->execute();

        $itemsData = $query->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($itemsData as $data) {
            $items[] = $this->hydrateListItem($data);
        }

        return $items;
    }

    public function createList(int $groupId, string $name, int $creatorId): ?int
    {
        $createdAt = date('Y-m-d H:i:s');

        $query = $this->conn->prepare(
            'INSERT INTO shopping_lists (group_id, name, created_by_user_id, created_at, updated_at) 
             VALUES (:groupId, :name, :creatorId, :createdAt, :createdAt) 
             RETURNING id'
        );
        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':creatorId', $creatorId, PDO::PARAM_INT);
        $query->bindParam(':createdAt', $createdAt);

        if ($query->execute()) {
            return $query->fetchColumn();
        }
        return null;
    }

    public function deleteList(int $listId): bool
    {
        $query = $this->conn->prepare('DELETE FROM shopping_lists WHERE id = :listId');
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        return $query->execute();
    }

    public function addItem(int $listId, string $name, string $subtitle = '', float $quantity = 1.0, string $unit = 'szt.'): ?int
    {
        $query = $this->conn->prepare(
            'INSERT INTO list_items (list_id, name, subtitle, quantity, unit) 
             VALUES (:listId, :name, :subtitle, :quantity, :unit) 
             RETURNING id'
        );
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':subtitle', $subtitle, PDO::PARAM_STR);
        $query->bindParam(':quantity', $quantity);
        $query->bindParam(':unit', $unit, PDO::PARAM_STR);

        if ($query->execute()) {
            return $query->fetchColumn();
        }
        return null;
    }

    public function toggleItemStatus(int $itemId, bool $isPurchased): bool
    {
        $query = $this->conn->prepare(
            'UPDATE list_items 
             SET is_purchased = :isPurchased 
             WHERE id = :itemId'
        );
        $val = (int)$isPurchased;
        $query->bindParam(':isPurchased', $val, PDO::PARAM_INT);
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        return $query->execute();
    }

    public function deleteItem(int $itemId): bool
    {
        $query = $this->conn->prepare('DELETE FROM list_items WHERE id = :itemId');
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getItemById(int $itemId): ?ListItem
    {
        $query = $this->conn->prepare(
            'SELECT * FROM list_items WHERE id = :itemId'
        );
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateListItem($data);
    }

    public function getGroupIdByItemId(int $itemId): ?int
    {
        $query = $this->conn->prepare(
            'SELECT sl.group_id 
             FROM list_items li
             JOIN shopping_lists sl ON li.list_id = sl.id
             WHERE li.id = :itemId'
        );
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchColumn();
        return $result ? (int)$result : null;
    }

    public function getGroupIdByListId(int $listId): ?int
    {
        $query = $this->conn->prepare(
            'SELECT group_id 
             FROM shopping_lists 
             WHERE id = :listId'
        );
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchColumn();
        return $result ? (int)$result : null;
    }

    public function updateListModificationDate(int $listId): bool
    {
        $dateModified = date('Y-m-d H:i:s');
        $query = $this->conn->prepare(
            'UPDATE shopping_lists 
             SET updated_at = :dateModified 
             WHERE id = :listId'
        );
        $query->bindParam(':dateModified', $dateModified);
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);

        return $query->execute();
    }
}