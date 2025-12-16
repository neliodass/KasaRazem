<?php


require_once "src/services/GroupService.php";
require_once "core/Auth.php";
require_once "repository/ExpenseRepository.php";
require_once 'src/dtos/ExpenseOutputDTO.php';
require_once 'src/dtos/CreateExpenseRequestDTO.php';

require_once "src/services/AuthService.php";
require_once "src/services/ExpenseService.php";

class ExpenseController extends AppController
{
    private static $instance;
    private ExpenseRepository $expenseRepository;
    private ExpenseService $expenseService;
    private GroupService $groupService;
    private AuthService $authService;

    private function __construct()
    {
        $this->expenseRepository = ExpenseRepository::getInstance();
        $this->groupService = GroupService::getInstance();
        $this->authService = AuthService::getInstance();
        $this->expenseService = ExpenseService::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function expenses($groupId)
    {
        Auth::requireLogin();
        $this->authService->verifyUserInGroup($groupId);
        $expenseOutputDtos = $this->expenseService->getExpensesSummaryList($groupId);

        $this->render('expenses', [
            'groupId' => $groupId,
            'expenses' => $expenseOutputDtos,
            'activeTab' => 'expenses',
            'groupName' => $this->groupService->getGroupName((string)$groupId)
        ]);
        exit();
    }

    public function addExpense($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);
        if (!$this->isPost()) {
            $users = $this->expenseService->getGroupUsers($groupId);
            $categories = $this->expenseService->getCategories();
            $userId = (int)Auth::userId();
            $this->render("addExpense", ["users" => $users, "categories" => $categories, "groupId" => $groupId, "userId" => $userId]);
            return;
        }
        $dto = CreateExpenseRequestDTO::fromPost();
        $this->expenseService->createExpense($groupId, $dto);

        $this->expenses($groupId);

    }

    public function getExpense($groupId, $expenseId)
    {
        $this->authService->verifyUserInGroup($groupId);
        $expenseDTO = $this->expenseService->getExpenseDetails((int)$groupId, (int)$expenseId);
        if (!$expenseDTO) {
            $this->redirect("/groups");
            return;
        }
        $this->render('expense_details', [
            'expenseDetails' => $expenseDTO,
            'groupId' => $groupId,
        ]);
    }

    public function deleteExpense($groupId, $expenseId)
    {
        if (!$this->isPost() || !isset($_POST['_method']) || $_POST['_method'] !== 'DELETE') {
            $this->redirect("/groups/" . $groupId . "/expenses");
            return;
        }

        $this->authService->verifyUserInGroup($groupId);
        $this->expenseService->deleteExpense((int)$groupId, (int)$expenseId);
        $this->redirect("/groups/" . $groupId . "/expenses");
    }

    public function editExpense($groupId, $expenseId)
    {
        $this->authService->verifyUserInGroup($groupId);
        $expense = $this->expenseRepository->getExpenseDetails((int)$expenseId);
        if (!$expense || (int)$expense['group_id'] !== (int)$groupId) {
            echo $expense['group_id'];
            echo $groupId;
            $this->redirect("/groups/" . $groupId . "/expenses");
            return;
        }
        $userId = (int)Auth::userId();
        $users = $this->expenseRepository->getUsersByGroupId($groupId);
        $categories = $this->expenseRepository->getCategories();
        $splitUserIds = array_column($expense['splits'], 'user_id');
        $this->render('editExpense', [
            'expense' => $expense,
            'users' => $users,
            'splitUserIds' => $splitUserIds,
            'groupId' => $groupId,
            'categories' => $categories,
            'userId' => $userId,
        ]);
    }

    public function updateExpense($groupId, $expenseId)
    {
        $this->authService->verifyUserInGroup($groupId);
        if (!$this->isPost()) {
            $this->redirect("/groups/" . $groupId . "/expenses");
            return;
        }
        $name = $_POST['name'] ?? '';
        $paidBy = (int)($_POST['paidBy'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $dateIncurred = $_POST['date'] ?? date('Y-m-d');
        $categoryId = (int)($_POST['category'] ?? 0);
        $selectedUserIds = [];
        if(empty($name)||$paidBy===0||$amount<=0||$categoryId===0){
            $this->redirect("/groups/" . $groupId . "/expenses/" . $expenseId . "/edit");
            return;
        }
        $splitUsers = $this->getSplitUsers($selectedUserIds);
        $success = $this->expenseRepository->updateExpense(
            $expenseId,
            $name,
            $paidBy,
            $amount,
            $dateIncurred,
            $categoryId,
            $splitUsers
        );
        $this->redirect("/groups/" . $groupId . "/expenses");
    }

    private function getSplitUsers(array $selectedUserIds): array
    {
        $prefix = 'split_user_';
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, $prefix) && $value > 0) {
                $userId = substr($key, strlen($prefix));
                $selectedUserIds[] = (int)$userId;
            }
        }

        $N = count($selectedUserIds);
        $splitUsers = [];
        if ($N > 0) {
            $fraction = 1.0 / $N;
            foreach ($selectedUserIds as $userId) {
                $splitUsers[] = [
                    'id' => $userId,
                    'fraction' => $fraction,
                ];
            }
        }
        return $splitUsers;
    }

}