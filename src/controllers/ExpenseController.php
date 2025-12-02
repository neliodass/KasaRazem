<?php
require_once "core/Auth.php";
require_once "repository/ExpenseRepository.php";

class ExpenseController extends AppController
{
    private static $instance;
    private $expenseRepository;
    private $groupRepository;
    private $groupController;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->groupRepository = GroupRepository::getInstance();
        $this->expenseRepository = ExpenseRepository::getInstance();
        $this->groupController = GroupController::getInstance();
    }
    public function addExpense($groupId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }
        if (!$this->isPost()) {
            $users = $this ->expenseRepository->getUsersByGroupId($groupId);
            $categories = $this ->expenseRepository->getCategories();
            $this->render("addExpense", ["users" => $users,"categories"=>$categories ,"groupId" => $groupId,"userId"=>$userId]);
            return;
        }
        $selectedUserIds = [];
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
        $this->expenseRepository->addExpense(
            $_POST['name'],
            $groupId,
            (int)$_POST['paidBy'],
            (float)$_POST['amount'],
            $_POST['date'],
            (int)$_POST['category'],
            $splitUsers);
        $this->groupController->groupDetails($groupId);
        return;

    }
}