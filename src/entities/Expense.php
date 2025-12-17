<?php

class Expense
{
    public int $id;
    public int $group_id;
    public int $paid_by_user_id;
    public float $amount; // NUMERIC(10, 2)
    public string $description;
    public ?int $category_id = null;
    public ?string $photo_url = null;
    public DateTimeInterface $date_incurred;

    public ?Group $group = null;
    public ?User $paidBy = null;
    public ?Category $category = null;

    /** @var ExpenseSplit[] */
    public array $splits = [];

    public static function fromArray(array $data): self
    {
        $e = new self();
        $e->id = isset($data['id']) ? (int)$data['id'] : 0;
        $e->group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
        $e->paid_by_user_id = isset($data['paid_by_user_id']) ? (int)$data['paid_by_user_id'] : 0;
        $e->amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $e->description = $data['description'] ?? '';
        $e->category_id = isset($data['category_id']) && $data['category_id'] !== null ? (int)$data['category_id'] : null;
        $e->photo_url = $data['photo_url'] ?? null;

        if (isset($data['date_incurred'])) {
            try {
                $e->date_incurred = new \DateTimeImmutable($data['date_incurred']);
            } catch (\Exception $ex) {
                $e->date_incurred = new \DateTimeImmutable();
            }
        } else {
            $e->date_incurred = new \DateTimeImmutable();
        }

        if (isset($data['group']) && is_array($data['group'])) {
            $e->group = Group::fromArray($data['group']);
        }
        if (isset($data['paid_by']) && is_array($data['paid_by'])) {
            $e->paidBy = User::fromArray($data['paid_by']);
        }
        if (isset($data['category']) && is_array($data['category'])) {
            $e->category = Category::fromArray($data['category']);
        }

        if (isset($data['splits']) && is_array($data['splits'])) {
            foreach ($data['splits'] as $s) {
                if (is_array($s)) {
                    $e->splits[] = ExpenseSplit::fromArray($s);
                } elseif ($s instanceof ExpenseSplit) {
                    $e->splits[] = $s;
                }
            }
        }

        return $e;
    }

    public function toArray(): array
    {
        $splits = [];
        foreach ($this->splits as $s) {
            if ($s instanceof ExpenseSplit) {
                $splits[] = $s->toArray();
            }
        }

        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'paid_by_user_id' => $this->paid_by_user_id,
            'amount' => $this->amount,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'photo_url' => $this->photo_url,
            'date_incurred' => $this->date_incurred instanceof \DateTimeInterface ? $this->date_incurred->format(DATE_ATOM) : null,
            'group' => $this->group ? $this->group->toArray() : null,
            'paid_by' => $this->paidBy ? $this->paidBy->toArray() : null,
            'category' => $this->category ? $this->category->toArray() : null,
            'splits' => $splits,
        ];
    }
}