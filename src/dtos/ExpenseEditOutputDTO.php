<?php

class ExpenseEditOutputDTO
{
    public int $id;
    public string $name;
    public float $amount;
    public string $date;
    public int $paidByUserId;
    public int $categoryId;
    public array $splitUserIds;
    public array $users;
    public array $categories;

    public function __construct(Expense $expense, array $users, array $categories)
    {
        $this->id = $expense->id;
        $this->name = $expense->description;
        $this->amount = $expense->amount;
        $this->date = $expense->date_incurred->format('Y-m-d');
        $this->paidByUserId = $expense->paid_by_user_id;
        $this->categoryId = $expense->category_id ?? 0;
        $this->splitUserIds = [];
        foreach ($expense->splits as $split) {
            $this->splitUserIds[] = $split->user_id;
        }

        $this->users = $users;
        $this->categories = $categories;
    }
}