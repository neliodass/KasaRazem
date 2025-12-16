<?php


require_once "src/services/GroupService.php";
require_once "core/Auth.php";
require_once "repository/ExpenseRepository.php";
require_once 'src/dtos/ExpenseOutputDTO.php';
require_once 'src/dtos/CreateExpenseRequestDTO.php';
require_once 'src/dtos/UpdateExpenseRequestDTO.php';

require_once "src/services/AuthService.php";
require_once "src/services/ExpenseService.php";

class ExpenseController extends AppController
{
    private static $instance;
    private ExpenseService $expenseService;
    private GroupService $groupService;
    private AuthService $authService;

    private function __construct()
    {
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

        $expenseDTO = $this->expenseService->getExpenseForEdit((int)$groupId, (int)$expenseId);

        if (!$expenseDTO) {
            $this->redirect("/groups/" . $groupId . "/expenses");
            return;
        }

        $userId = (int)Auth::userId();

        $this->render('editExpense', [
            'expense' => $expenseDTO,
            'users' => $expenseDTO->users,
            'splitUserIds' => $expenseDTO->splitUserIds,
            'groupId' => $groupId,
            'categories' => $expenseDTO->categories,
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

        $dto = UpdateExpenseRequestDTO::fromPost();

        if (!$dto->validate()) {
            $this->redirect("/groups/" . $groupId . "/expenses/" . $expenseId . "/edit");
            return;
        }

        $success = $this->expenseService->updateExpense((int)$groupId, (int)$expenseId, $dto);

        if (!$success) {
            $this->redirect("/groups/" . $groupId . "/expenses/" . $expenseId . "/edit");
            return;
        }

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