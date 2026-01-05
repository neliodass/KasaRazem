<?php

class CreateExpenseRequestDTO
{
    public string $name;
    public float $amount;
    public int $paidByUserId;
    public string $date;
    public int $categoryId;
    public array $splitUserIds = [];
    public string $splitMode = 'equal'; // 'equal', 'ratio', 'amount'
    public array $splitRatios = []; // userId => ratio
    public array $splitAmounts = [];

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->amount = (float)($data['amount'] ?? 0);
        $this->paidByUserId = (int)($data['paidBy'] ?? 0);
        $this->date = $data['date'] ?? '';
        $this->categoryId = (int)($data['category'] ?? 0);
        $this->splitMode = $data['split_mode'] ?? 'equal';
        $this->extractSplitUserIds($data);
        $this->extractSplitRatios($data);
        $this->extractSplitAmounts($data);
    }

    public static function fromPost(): self
    {
        return new self($_POST);
    }

    private function extractSplitUserIds(array $data): void
    {
        $prefix = 'split_user_';
        foreach ($data as $key => $value) {
            if (str_starts_with($key, $prefix) && $value > 0) {
                $userId = substr($key, strlen($prefix));
                $this->splitUserIds[] = (int)$userId;
            }
        }
    }

    private function extractSplitRatios(array $data): void
    {
        $prefix = 'split_ratio_';
        foreach ($data as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $userId = (int)substr($key, strlen($prefix));
                $ratio = (int)($value ?? 1);
                if ($ratio < 1) $ratio = 1;
                $this->splitRatios[$userId] = $ratio;
            }
        }
    }

    private function extractSplitAmounts(array $data): void
    {
        $prefix = 'split_amount_';
        foreach ($data as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $userId = (int)substr($key, strlen($prefix));
                $amount = (float)($value ?? 0);
                $this->splitAmounts[$userId] = $amount;
            }
        }
    }
}