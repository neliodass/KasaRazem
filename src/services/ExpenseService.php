<?php

require_once 'repository/ExpenseRepository.php';
require_once 'src/dtos/ExpenseOutputDTO.php';
require_once "src/IconsHelper.php";
require_once "src/ColorHelper.php";

class ExpenseService
{
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getExpensesSummaryList(int $groupId): array
    {
        $expenseRepository = ExpenseRepository::getInstance();
        $expenses = $expenseRepository->getExpensesByGroupId($groupId);
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
}