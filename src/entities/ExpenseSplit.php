<?php


class ExpenseSplit
{
    public int $id;
    public int $expense_id;
    public int $user_id;
    public float $amount_owed = 0.00; // NUMERIC(10, 2)
    public string $split_type = 'equal'; // 'equal', 'exact', 'percentage'
    public ?Expense $expense = null;
    public ?User $user = null;

    public static function fromArray(array $data): self
    {
        $es = new self();
        $es->id = isset($data['id']) ? (int)$data['id'] : 0;
        $es->expense_id = isset($data['expense_id']) ? (int)$data['expense_id'] : 0;
        $es->user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $es->amount_owed = isset($data['amount_owed']) ? (float)$data['amount_owed'] : 0.0;
        $es->split_type = $data['split_type'] ?? 'equal';

        if (isset($data['expense']) && is_array($data['expense'])) {
            $es->expense = Expense::fromArray($data['expense']);
        }
        if (isset($data['user']) && is_array($data['user'])) {
            $es->user = User::fromArray($data['user']);
        }

        return $es;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'expense_id' => $this->expense_id,
            'user_id' => $this->user_id,
            'amount_owed' => $this->amount_owed,
            'split_type' => $this->split_type,
            'expense' => $this->expense ? $this->expense->toArray() : null,
            'user' => $this->user ? $this->user->toArray() : null,
        ];
    }

}