<?php

class UpdateExpenseRequestDTO
{
    public string $name;
    public int $paidByUserId;
    public float $amount;
    public string $date;
    public int $categoryId;
    public array $splitUserIds;
    public string $splitMode = 'equal'; // 'equal', 'ratio', 'amount'
    public array $splitRatios = []; // userId => ratio
    public array $splitAmounts = [];

    public static function fromPost(): self
    {
        $dto = new self();
        $dto->name = $_POST['name'] ?? '';
        $dto->paidByUserId = (int)($_POST['paidBy'] ?? 0);
        $dto->amount = (float)($_POST['amount'] ?? 0);
        $dto->date = $_POST['date'] ?? date('Y-m-d');
        $dto->categoryId = (int)($_POST['category'] ?? 0);
        $dto->splitMode = $_POST['split_mode'] ?? 'equal';

        $dto->splitUserIds = [];
        $prefix = 'split_user_';
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, $prefix) && $value > 0) {
                $userId = substr($key, strlen($prefix));
                $dto->splitUserIds[] = (int)$userId;
            }
        }
        $ratioPrefix = 'split_ratio_';
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, $ratioPrefix)) {
                $userId = (int)substr($key, strlen($ratioPrefix));
                $ratio = (int)($value ?? 1);
                if ($ratio < 1) $ratio = 1; // minimum 1
                $dto->splitRatios[$userId] = $ratio;
            }
        }

        $amountPrefix = 'split_amount_';
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, $amountPrefix)) {
                $userId = (int)substr($key, strlen($amountPrefix));
                $amount = (float)($value ?? 0);
                $dto->splitAmounts[$userId] = $amount;
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
