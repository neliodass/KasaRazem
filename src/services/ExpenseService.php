<?php

require_once 'repository/ExpenseRepository.php';
require_once 'src/dtos/ExpenseOutputDTO.php';
require_once 'src/dtos/CreateExpenseRequestDTO.php';
require_once "src/IconsHelper.php";
require_once "src/ColorHelper.php";

class ExpenseService
{
    private static $instance = null;
    private ExpenseRepository $expenseRepository;
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
        return $this->expenseRepository->getUsersByGroupId($groupId);
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
            $fraction = 1.0 / $count;
            foreach ($dto->splitUserIds as $userId) {
                $splitUsers[] = [
                    'id' => $userId,
                    'fraction' => $fraction,
                ];
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
        $expenseData = $this->expenseRepository->getExpenseDetails($expenseId);

        if (!$expenseData) {
            return null;
        }
        if ((int)$expenseData['group_id'] !== $groupId) {
            return null;
        }

        $categoryId = $expenseData['category_id'];
        $icon = IconsHelper::$expenseIcon[$categoryId] ?? 'default-icon';

        $colors = ColorHelper::generatePastelColorSet();

        return new ExpenseDetailsOutputDTO($expenseData, $icon, $colors);
    }
    public function deleteExpense(int $groupId, int $expenseId): void
    {
        $expense = $this->expenseRepository->getExpenseDetails($expenseId);

        if (!$expense || (int)$expense['group_id'] !== $groupId) {
            return;
        }

        $this->expenseRepository->deleteExpense($expenseId);
    }
}