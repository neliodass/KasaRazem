<?php

require_once 'src/dtos/SettlementItemDTO.php';

class SettlementsDetailsOutputDTO
{
    /** @var SettlementItemDTO[] */
    public array $settlements;
    public string $groupName;
    public int $groupId;

    public function __construct(array $settlements, string $groupName, int $groupId)
    {
        $this->settlements = $settlements;
        $this->groupName = $groupName;
        $this->groupId = $groupId;
    }
}

