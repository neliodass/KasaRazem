<?php


class ExpenseSplit
{
    public int $id;
    public int $expense_id;
    public int $user_id;
    public float $amount_owed = 0.00; // NUMERIC(10, 2)
    public string $split_type = 'equal'; // 'equal', 'exact', 'percentage'
    public ?Expense $expense = null;
    public ?User $user = null;

}