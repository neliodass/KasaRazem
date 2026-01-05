<?php

require_once "src/entities/Expense.php";
require_once "repository/CategoryRepository.php";
require_once "src/entities/ExpenseSplit.php";
require_once "src/entities/Settlement.php";

class ExpenseRepository extends Repository
{
    private static $instance;
    private UserRepository $userRepository;
    private CategoryRepository $categoryRepository;

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
        $this->categoryRepository = CategoryRepository::getInstance();
    }

    private function hydrateExpense(array $data): Expense
    {
        $expense = Expense::fromArray($data);

        if (isset($data['paid_by_user_id'])) {
            $expense->paidBy = $this->userRepository->getUserById((string)$data['paid_by_user_id']);
        } else {
            $expense->paidBy = null;
        }

        if (isset($expense->category_id)) {
            $expense->category = $this->categoryRepository->getCategoryById($expense->category_id);
        } else {
            $expense->category = null;
        }

        if (isset($expense->id)) {
            $expense->splits = $this->getSplitsByExpenseId($expense->id);
        } else {
            $expense->splits = [];
        }

        return $expense;
    }

    private function getSplitsByExpenseId(int $expenseId): array
    {
        $query = $this->conn->prepare(
            'SELECT es.id, es.expense_id, es.user_id, es.amount_owed, es.split_type,
                    u.id AS user_id, u.firstname, u.lastname, u.email
            FROM expense_splits es
            JOIN users u ON es.user_id = u.id
            WHERE es.expense_id = :expenseId'
        );
        $query->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);
        $query->execute();
        $splitsData = $query->fetchAll(PDO::FETCH_ASSOC);

        $splits = [];
        foreach ($splitsData as $sData) {
            $splitData = [
                'id' => $sData['id'] ?? null,
                'expense_id' => $sData['expense_id'] ?? null,
                'user_id' => $sData['user_id'] ?? null,
                'amount_owed' => $sData['amount_owed'] ?? 0,
                'split_type' => $sData['split_type'] ?? 'equal',
                'user' => [
                    'id' => $sData['user_id'] ?? null,
                    'firstname' => $sData['firstname'] ?? '',
                    'lastname' => $sData['lastname'] ?? '',
                    'email' => $sData['email'] ?? '',
                ],
            ];

            $split = ExpenseSplit::fromArray($splitData);
            $splits[] = $split;
        }
        return $splits;
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->getAll();
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
            'SELECT e.*,u.firstname,u.lastname FROM expenses e 
           join users u on e.paid_by_user_id = u.id
         WHERE e.group_id = :group_id
         ORDER BY e.date_incurred DESC'
        );

        $expensesQuery->bindParam(':group_id', $groupId);
        $expensesQuery->execute();
        $expenseData =  $expensesQuery->fetchAll(PDO::FETCH_ASSOC);
        $expenseOutputs = [];
        foreach ($expenseData as $expense) {
            $expenseOutputs[] = $this->hydrateExpense($expense);
        }
        return $expenseOutputs;
    }
    public function getDebtDataByGroupId(int $groupId): array
    {
        $debtorsQuery = $this->conn->prepare(
            'SELECT es.user_id AS debtor_id, SUM(es.amount_owed) AS total_owed
         FROM expense_splits es
         JOIN expenses e ON es.expense_id = e.id
         WHERE e.group_id = :groupId
         GROUP BY es.user_id'
        );
        $debtorsQuery->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $debtorsQuery->execute();
        $debtors = $debtorsQuery->fetchAll(PDO::FETCH_KEY_PAIR);

        $creditorsQuery = $this->conn->prepare(
            'SELECT paid_by_user_id AS creditor_id, SUM(amount) AS total_paid
         FROM expenses
         WHERE group_id = :groupId
         GROUP BY paid_by_user_id'
        );
        $creditorsQuery->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $creditorsQuery->execute();
        $creditors = $creditorsQuery->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'owed' => $debtors,
            'paid' => $creditors,
        ];
    }
    public function getSettlementsByGroupId(int $groupId): array
    {
        $query = $this->conn->prepare(
            'SELECT * FROM settlements WHERE group_id = :groupId'
        );
        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->execute();
        $settlementsData = $query->fetchAll(PDO::FETCH_ASSOC);

        $settlements = [];
        foreach ($settlementsData as $data) {
            $settlements[] = Settlement::fromArray($data);
        }

        return $settlements;
    }
    public function getExpenseDetails(int $expenseId): ?Expense
    {
        $query = $this->conn->prepare(
            'SELECT e.*
            FROM expenses e
            WHERE e.id = :expenseId'
        );
        $query->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        return $this->hydrateExpense($data);
    }
    public function deleteExpense(int $expenseId): bool
    {
        $query = $this->conn->prepare(
            'DELETE FROM expenses WHERE id = :expenseId'
        );
        $query->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);
        return $query->execute();
    }
    public function updateExpense(int $expenseId, string $name, int $paidByUserId, float $amount, $date, int $categoryId, array $splitUsers): bool
    {
        $this->conn->beginTransaction();

        $updateExpenseQuery = $this->conn->prepare(
            'UPDATE expenses 
             SET description = :name, paid_by_user_id = :paidByUserId, amount = :amount, date_incurred = :dateIncurred, category_id = :categoryId
             WHERE id = :expenseId'
        );

        $updateExpenseQuery->bindParam(':name', $name, PDO::PARAM_STR);
        $updateExpenseQuery->bindParam(':paidByUserId', $paidByUserId, PDO::PARAM_INT);
        $updateExpenseQuery->bindParam(':amount', $amount);
        $updateExpenseQuery->bindParam(':dateIncurred', $date);
        $updateExpenseQuery->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $updateExpenseQuery->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);

        if (!$updateExpenseQuery->execute()) {
            $this->conn->rollBack();
            return false;
        }

        $deleteSplitsQuery = $this->conn->prepare(
            'DELETE FROM expense_splits WHERE expense_id = :expenseId'
        );
        $deleteSplitsQuery->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);
        if (!$deleteSplitsQuery->execute()) {
            $this->conn->rollBack();
            return false;
        }

        foreach ($splitUsers as $user) {
            $insertSplitQuery = $this->conn->prepare(
                'INSERT INTO expense_splits (expense_id, user_id, amount_owed) 
                 VALUES (:expenseId, :userId, :amount_owed)'
            );
            $amount_owed = $amount * $user['fraction'];
            $insertSplitQuery->bindParam(':amount_owed', $amount_owed);
            $insertSplitQuery->bindParam(':expenseId', $expenseId, PDO::PARAM_INT);
            $insertSplitQuery->bindParam(':userId', $user['id'], PDO::PARAM_INT);
            if (!$insertSplitQuery->execute()) {
                $this->conn->rollBack();
                return false;
            }
        }
        $this->conn->commit();
        return true;
    }

}