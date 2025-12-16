<?php

class UpdateExpenseRequestDTO
{
    public string $name;
    public int $paidByUserId;
    public float $amount;
    public string $date;
    public int $categoryId;
    public array $splitUserIds;

    public static function fromPost(): self
    {
        $dto = new self();
        $dto->name = $_POST['name'] ?? '';
        $dto->paidByUserId = (int)($_POST['paidBy'] ?? 0);
        $dto->amount = (float)($_POST['amount'] ?? 0);
        $dto->date = $_POST['date'] ?? date('Y-m-d');
        $dto->categoryId = (int)($_POST['category'] ?? 0);

        $dto->splitUserIds = [];
        $prefix = 'split_user_';
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, $prefix) && $value > 0) {
                $userId = substr($key, strlen($prefix));
                $dto->splitUserIds[] = (int)$userId;
            }
        }

        return $dto;
    }

    public function validate(): bool
    {
        return !empty($this->name)
            && $this->paidByUserId > 0
            && $this->amount > 0
            && $this->categoryId > 0
            && !empty($this->splitUserIds);
    }
}

