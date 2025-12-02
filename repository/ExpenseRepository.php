<?php

require_once "repository/Repository.php";

class ExpenseRepository extends Repository
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUsersByGroupId(int $groupId): ?array
    {
        $query = $this->conn->prepare(
            'SELECT u.* FROM users u
            JOIN group_members gm ON u.id = gm.user_id
            WHERE gm.group_id = :groupId'
        );

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
    public function getCategories(): ?array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM categories'
        );

        $query->execute();

        $categories = $query->fetchAll(PDO::FETCH_ASSOC);

        return $categories;
    }
    public function addExpense(string $name,int $groupId, int $paidByUserId,float $amount,$date,$categoryId,Array $splitUsers): ?int
    {
        $this->conn->beginTransaction();
        $query = $this->conn->prepare(
            'INSERT INTO expenses (group_id, paid_by_user_id, amount, date_incurred,category_id,description) 
                 VALUES (:groupId, :paidByUserId, :amount, :dateIncurred,:categoryId,:name)
                 RETURNING id'
        );

        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':paidByUserId', $paidByUserId, PDO::PARAM_INT);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':dateIncurred', $date);
        $query->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $query->execute();

        $newExpenseId = $query->fetchColumn();

        if (!$newExpenseId) {
            $this->conn->rollBack();
            return null;
        }
        foreach ($splitUsers as $user) {
            $query = $this->conn->prepare(
                'INSERT INTO expense_splits (expense_id, user_id, amount_owed) 
                 VALUES (:expenseId, :userId, :amount_owed)'
            );
            $amount_owed = $amount * $user['fraction'];
            $query->bindParam(':amount_owed', $amount_owed);
            $query->bindParam(':expenseId', $newExpenseId, PDO::PARAM_INT);
            $query->bindParam(':userId', $user['id'], PDO::PARAM_INT);
            $query->execute();
        }
        $this->conn->commit();
        return $groupId;


    }
    public function getExpensesByGroupId(int $groupId): ?array
    {
        $expensesQuery = $this->conn->prepare(
            'SELECT e.* FROM expenses e 
         WHERE e.group_id = :group_id
         ORDER BY e.date_incurred DESC'
        );
        $expensesQuery->bindParam(':group_id', $groupId);
        $expensesQuery->execute();
        return $expensesQuery->fetchAll(PDO::FETCH_ASSOC);
    }

}