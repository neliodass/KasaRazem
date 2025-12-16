<?php

class ExpenseEditOutputDTO
{
    public int $id;
    public string $name;
    public float $amount;
    public string $date;
    public int $paidByUserId;
    public int $categoryId;
    public array $splitUserIds;
    public string $splitMode = 'equal'; // 'equal', 'ratio', 'amount'
    public array $splitRatios = []; // userId => ratio
    public array $splitAmounts = [];
    /** @var User[] */
    public array $users;
    /** @var Category[] */
    public array $categories;

    public function __construct(Expense $expense, array $users, array $categories)
    {
        $this->id = $expense->id;
        $this->name = $expense->description;
        $this->amount = $expense->amount;
        $this->date = $expense->date_incurred->format('Y-m-d');
        $this->paidByUserId = $expense->paid_by_user_id;
        $this->categoryId = $expense->category_id ?? 0;

        $this->splitUserIds = [];
        foreach ($expense->splits as $split) {
            $this->splitUserIds[] = $split->user_id;
        }

        $this->detectSplitMode($expense);
        $this->users = $users;
        $this->categories = $categories;
    }
    private function detectSplitMode(Expense $expense): void
    {
        if (empty($expense->splits)) {
            $this->splitMode = 'equal';
            return;
        }
        $fractions = [];
        foreach ($expense->splits as $split) {
            $fraction = $split->amount_owed / $expense->amount;
            $fractions[$split->user_id] = $fraction;
            $this->splitAmounts[$split->user_id] = $split->amount_owed;
        }
        $firstFraction = reset($fractions);
        $isEqual = true;
        foreach ($fractions as $fraction) {
            if (abs($fraction - $firstFraction) > 0.001) {
                $isEqual = false;
                break;
            }
        }

        if ($isEqual) {
            $this->splitMode = 'equal';
            foreach ($fractions as $userId => $fraction) {
                $this->splitRatios[$userId] = 1;
            }
        } else {
            $ratios = $this->calculateRatios($fractions);
            $allInteger = true;
            $maxRatio = 0;
            foreach ($ratios as $ratio) {
                if ($ratio != floor($ratio) || $ratio > 100) {
                    $allInteger = false;
                    break;
                }
                $maxRatio = max($maxRatio, $ratio);
            }

            if ($allInteger && $maxRatio <= 100) {
                $this->splitMode = 'ratio';
                $this->splitRatios = $ratios;
            } else {
                $this->splitMode = 'amount';
                foreach ($fractions as $userId => $fraction) {
                    $this->splitRatios[$userId] = 1;
                }
            }
        }
    }

    private function calculateRatios(array $fractions): array
    {
        $ratios = [];
        $minFraction = min($fractions);
        foreach ($fractions as $userId => $fraction) {
            $ratio = round($fraction / $minFraction);
            if ($ratio < 1) $ratio = 1;
            $ratios[$userId] = (int)$ratio;
        }
        return $ratios;
    }
}