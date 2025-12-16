<?php


class SettlementItemDTO
{
    public int $fromUserId;
    public string $fromUserName;
    public int $toUserId;
    public string $toUserName;
    public float $amount;

    public function __construct(int $fromUserId, string $fromUserName, int $toUserId, string $toUserName, float $amount)
    {
        $this->fromUserId = $fromUserId;
        $this->fromUserName = $fromUserName;
        $this->toUserId = $toUserId;
        $this->toUserName = $toUserName;
        $this->amount = $amount;
    }
}
