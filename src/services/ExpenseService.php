<?php

require_once 'repository/ExpenseRepository.php';
require_once 'repository/GroupRepository.php';
require_once 'src/dtos/ExpenseOutputDTO.php';
require_once 'src/dtos/CreateExpenseRequestDTO.php';
require_once 'src/dtos/ExpenseEditOutputDTO.php';
require_once 'src/dtos/UpdateExpenseRequestDTO.php';
require_once "src/IconsHelper.php";
require_once "src/ColorHelper.php";

class ExpenseService
{
    private static $instance = null;
    private ExpenseRepository $expenseRepository;
    private GroupRepository $groupRepository;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->expenseRepository = ExpenseRepository::getInstance();
        $this->groupRepository = GroupRepository::getInstance();
    }

    public function getExpensesSummaryList(int $groupId): array
    {
        $expenses = $this->expenseRepository->getExpensesByGroupId($groupId);
        $expenseOutputDtos = [];

        foreach ($expenses as $expense) {
            $dto = new ExpenseSummaryOutputDto();
            $dto->id = $expense->id;
            $dto->name = $expense->description;
            $dto->amount = $expense->amount;
            $paidBy = $expense->paidBy->firstname . ' ' . $expense->paidBy->lastname;
            $dto->paidBy = $expense->paidBy ? $paidBy : 'Unknown';
            $dto->dateIncurred = $expense->date_incurred->format('d-m-Y');
            if ($expense->category_id) {
                $dto->icon = IconsHelper::$expenseIcon[$expense->category_id];
                $colors = ColorHelper::generatePastelColorSet();
                $dto->iconBgColor = $colors['background'];
                $dto->iconColor = $colors['icon'];
                $dto->categoryId = $expense->category_id;
            } else {
                $dto->icon = 'default-icon';
                $dto->iconBgColor = '#FFFFFF';
                $dto->iconColor = '#000000';
            }

            $expenseOutputDtos[] = $dto;
        }

        return $expenseOutputDtos;
    }
    public function getGroupUsers(int $groupId): array
    {
        return $this->groupRepository->getUsersByGroupId($groupId);
    }

    public function getCategories(): array
    {
        return $this->expenseRepository->getCategories();
    }
    public function createExpense(int $groupId, CreateExpenseRequestDTO $dto): void
    {
        $splitUsers = [];
        $count = count($dto->splitUserIds);

        if ($count > 0) {
            if ($dto->splitMode === 'ratio') {
                $totalRatio = 0;
                foreach ($dto->splitUserIds as $userId) {
                    $ratio = $dto->splitRatios[$userId] ?? 1;
                    $totalRatio += $ratio;
                }

                foreach ($dto->splitUserIds as $userId) {
                    $ratio = $dto->splitRatios[$userId] ?? 1;
                    $fraction = $ratio / $totalRatio;
                    $splitUsers[] = [
                        'id' => $userId,
                        'fraction' => $fraction,
                    ];
                }
            } else if( $dto->splitMode === 'equal') {
                $fraction = 1.0 / $count;
                foreach ($dto->splitUserIds as $userId) {
                    $splitUsers[] = [
                        'id' => $userId,
                        'fraction' => $fraction,
                    ];
                }
            }
        }

        $this->expenseRepository->addExpense(
            $dto->name,
            $groupId,
            $dto->paidByUserId,
            $dto->amount,
            $dto->date,
            $dto->categoryId,
            $splitUsers
        );
    }
    public function getExpenseDetails(int $groupId, int $expenseId): ?ExpenseDetailsOutputDTO
    {
        $expense = $this->expenseRepository->getExpenseDetails($expenseId);

        if (!$expense || $expense->group_id !== $groupId) {
            return null;
        }

        $categoryId = $expense->category_id ?? 0;
        $icon = IconsHelper::$expenseIcon[$categoryId] ?? 'default-icon';

        $colors = ColorHelper::generatePastelColorSet();

        return new ExpenseDetailsOutputDTO($expense, $icon, $colors);
    }
    public function deleteExpense(int $groupId, int $expenseId): void
    {
        $expense = $this->expenseRepository->getExpenseDetails($expenseId);

        if (!$expense || $expense->group_id !== $groupId) {
            return;
        }

        $this->expenseRepository->deleteExpense($expenseId);
    }
    public function getExpenseForEdit(int $groupId, int $expenseId): ?ExpenseEditOutputDTO
    {
        $expense = $this->expenseRepository->getExpenseDetails($expenseId);

        if (!$expense || $expense->group_id !== $groupId) {
            return null;
        }

        $users = $this->groupRepository->getUsersByGroupId($groupId);
        $categories = $this->expenseRepository->getCategories();

        return new ExpenseEditOutputDTO($expense, $users, $categories);
    }
    public function updateExpense(int $groupId, int $expenseId, UpdateExpenseRequestDTO $dto): bool
    {
        $expense = $this->expenseRepository->getExpenseDetails($expenseId);
        if (!$expense || $expense->group_id !== $groupId) {
            return false;
        }
        if (!$dto->validate()) {
            return false;
        }

        $splitUsers = [];
        $count = count($dto->splitUserIds);

        if ($count > 0) {
            if ($dto->splitMode === 'ratio') {
                $totalRatio = 0;
                foreach ($dto->splitUserIds as $userId) {
                    $ratio = $dto->splitRatios[$userId] ?? 1;
                    $totalRatio += $ratio;
                }

                foreach ($dto->splitUserIds as $userId) {
                    $ratio = $dto->splitRatios[$userId] ?? 1;
                    $fraction = $ratio / $totalRatio;
                    $splitUsers[] = [
                        'id' => $userId,
                        'fraction' => $fraction,
                    ];
                }
            } else if( $dto->splitMode === 'equal') {
                $fraction = 1.0 / $count;
                foreach ($dto->splitUserIds as $userId) {
                    $splitUsers[] = [
                        'id' => $userId,
                        'fraction' => $fraction,
                    ];
                }
            }
        }

        return $this->expenseRepository->updateExpense(
            $expenseId,
            $dto->name,
            $dto->paidByUserId,
            $dto->amount,
            $dto->date,
            $dto->categoryId,
            $splitUsers
        );
    }
}