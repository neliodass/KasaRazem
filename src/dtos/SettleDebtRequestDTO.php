<?php

class SettleDebtRequestDTO
{
    public int $payerId;
    public int $payeeId;
    public float $amount;
    public int $groupId;

    public static function fromPost(int $groupId): self
    {
        $dto = new self();
        $dto->groupId = $groupId;
        $dto->payerId = (int)($_POST['payer_id'] ?? 0);
        $dto->payeeId = (int)($_POST['payee_id'] ?? 0);
        $dto->amount = (float)($_POST['amount'] ?? 0.0);
        
        return $dto;
    }

    public function validate(int $currentUserId): bool
    {
        if ($this->amount <= 0) {
            return false;
        }
        if ($this->payerId === $this->payeeId) {
            return false;
        }
        if ($this->payerId !== $currentUserId && $this->payeeId !== $currentUserId) {
            return false;
        }
        
        return true;
    }
}



