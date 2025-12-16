<?php

class CreateExpenseRequestDTO
{
    public string $name;
    public float $amount;
    public int $paidByUserId;
    public string $date;
    public int $categoryId;
    public array $splitUserIds = [];

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->amount = (float)($data['amount'] ?? 0);
        $this->paidByUserId = (int)($data['paidBy'] ?? 0);
        $this->date = $data['date'] ?? '';
        $this->categoryId = (int)($data['category'] ?? 0);
        $this->extractSplitUserIds($data);
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
}