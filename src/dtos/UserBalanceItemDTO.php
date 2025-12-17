<?php

class UserBalanceItemDTO
{
    public int $userId;
    public string $userName;
    public float $netBalance;
    public bool $isCurrentUser;
    public ?string $profile_picture;

    public function __construct(int $userId, string $userName, float $netBalance, bool $isCurrentUser, ?string $profile_picture = null)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->netBalance = $netBalance;
        $this->isCurrentUser = $isCurrentUser;
        $this->profile_picture = $profile_picture;
    }
}