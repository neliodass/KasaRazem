<?php

class ExpenseSplitOutputDTO
{
    public int $userId;
    public string $userFullName;
    public float $amountOwed;

    public function __construct(ExpenseSplit $split)
    {
        $this->userId = $split->user_id;
        $this->userFullName = $split->user
            ? $split->user->firstname . ' ' . $split->user->lastname
            : 'Unknown';
        $this->amountOwed = $split->amount_owed;
    }
}

