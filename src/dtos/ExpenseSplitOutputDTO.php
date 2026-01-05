<?php

class ExpenseSplitOutputDTO
{
    public int $userId;
    public string $userFullName;
    public float $amountOwed;
    public ?string $profilePicture;

    public function __construct(ExpenseSplit $split)
    {
        $this->userId = $split->user_id;
        $this->userFullName = $split->user
            ? $split->user->firstname . ' ' . $split->user->lastname
            : 'Unknown';
        $this->amountOwed = $split->amount_owed;
        $this->profilePicture = $split->user->profile_picture ?? null;
    }
}
