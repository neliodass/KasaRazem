<?php



require_once "src/services/GroupService.php";
require_once "core/Auth.php";
require_once "repository/ExpenseRepository.php";
require_once "src/IconsHelper.php";
require_once "src/ColorHelper.php";

class ExpenseController extends AppController
{
    private static $instance;
    private ExpenseRepository $expenseRepository;
    private GroupRepository $groupRepository;
    private GroupController $groupController;
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
        $this->groupRepository = GroupRepository::getInstance();
        $this->expenseRepository = ExpenseRepository::getInstance();
        $this->groupController = GroupController::getInstance();
        $this->groupService = GroupService::getInstance();
    }
    public function expenses($groupId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }

        $expenses = $this->expenseRepository->getExpensesByGroupId($groupId);
        foreach ($expenses as &$expense) {
            $expense['icon'] = IconsHelper::$expenseIcon[$expense['category_id']];
            $colors = ColorHelper::generatePastelColorSet();
            $expense['icon_bg_color'] = $colors['background'];
            $expense['icon_color'] = $colors['icon'];
            $expense['paidBy'] = $expense['firstname'].' '.$expense['lastname'];
            $dateToFormat =  date('d-m-Y', strtotime($expense['date_incurred']));
            $expense['date_incurred'] = str_replace('-', '.', $dateToFormat);
        }
        $this->render('expenses', [
            'groupId' => $groupId,
            'expenses' => $expenses,
            'activeTab' => 'expenses',
            'groupName' => $this->groupService->getGroupName((string)$groupId)
        ]);
        exit();
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
    public function getExpense($groupId,$expenseId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }
        $expenseId = (int)$expenseId;
        $expenseDetails = $this->expenseRepository->getExpenseDetails($expenseId);
        if (!$expenseDetails) {
            $this->redirect("/groups");
        }
        $expenseIcon = IconsHelper::$expenseIcon[$expenseDetails['category_id']];
        $expenseIconColors = ColorHelper::generatePastelColorSet();
        $this->render('expense_details', [
            'expenseDetails' => $expenseDetails,
            'expenseIcon' => $expenseIcon,
            'expenseIconColors' => $expenseIconColors,
            'groupId' => $groupId,
        ]);
    }
    public function deleteExpense($groupId,$expenseId)
    {
        if(!$this->isPost()){
            $this->redirect("/groups/".$groupId."/expenses");
            return;
        }
        if (!isset($_POST['_method']) || $_POST['_method'] !== 'DELETE') {
            $this->redirect("/groups/" . $groupId . "/expenses");
            return;
        }
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $userId = (int)Auth::userId();
        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            header("Location: /groups");
            exit;
        }

        $expenseId = (int)$expenseId;
        $this->expenseRepository->deleteExpense($expenseId);
        $this->redirect("/groups/".$groupId."/expenses");
    }

}