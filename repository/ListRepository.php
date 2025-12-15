<?php

class ListRepository extends Repository
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getListsHeadersByGroupIdOrderByDate(int $groupId): array
    {
        $query = $this->conn->prepare(
            'SELECT id,name FROM shopping_lists WHERE group_id = :groupId ORDER BY updated_at DESC'
        );

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->execute();

        $lists = $query->fetchAll(PDO::FETCH_ASSOC);

        return $lists;
    }
    public function getListItems(int $listId): array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM list_items 
             WHERE list_id = :listId 
             ORDER BY is_purchased ASC, id DESC'
        );
        $query->bindParam(':listId', $listId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function createList(int $groupId, string $name, int $creatorId): ?int
    {
        $createdAt = date('Y-m-d H:i:s');

        $query = $this->conn->prepare(
            'INSERT INTO shopping_lists (group_id, name, created_by_user_id,created_at) 
             VALUES (:groupId, :name, :creatorId,:createdAt) 
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
        $query->bindParam(':isPurchased', $val,PDO::PARAM_INT);
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        return $query->execute();
    }
    public function deleteItem(int $itemId): bool
    {
        $query = $this->conn->prepare('DELETE FROM list_items WHERE id = :itemId');
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        return $query->execute();
    }
    public function getGroupIdByItemId(int $itemId): ?int {
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
    public function getItemById(int $itemId): ?array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM list_items WHERE id = :itemId'
        );
        $query->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $query->execute();

        $item = $query->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }
    public function getGroupIdByListId(int $listId): ?int {
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
}