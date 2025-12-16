<?php

class UserBalanceItemDTO
{
    public int $userId;
    public string $userName;
    public float $netBalance;
    public bool $isCurrentUser;

    public function __construct(int $userId, string $userName, float $netBalance, bool $isCurrentUser)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->netBalance = $netBalance;
        $this->isCurrentUser = $isCurrentUser;
    }
}