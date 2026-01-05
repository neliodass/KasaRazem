<?php

require_once 'src/dtos/UserBalanceItemDTO.php';

class BalanceSummaryOutputDTO
{
    /** @var UserBalanceItemDTO[] */
    public array $balanceItems;
    public float $currentUserNetBalance;
    public string $currentUserBalanceEmoji;
    public string $groupName;
    public int $groupId;

    public function __construct(
        array $balanceItems,
        float $currentUserNetBalance,
        string $currentUserBalanceEmoji,
        string $groupName,
        int $groupId
    ) {
        $this->balanceItems = $balanceItems;
        $this->currentUserNetBalance = $currentUserNetBalance;
        $this->currentUserBalanceEmoji = $currentUserBalanceEmoji;
        $this->groupName = $groupName;
        $this->groupId = $groupId;
    }
}




