<?php


class Category
{
    public int $id;
    public string $name;
    public array $expenses = [];

    public static function fromArray(array $data): self
    {
        $c = new self();
        $c->id = isset($data['id']) ? (int)$data['id'] : 0;
        $c->name = $data['name'] ?? '';

        if (isset($data['expenses']) && is_array($data['expenses'])) {
            foreach ($data['expenses'] as $e) {
                if (is_array($e)) {
                    $c->expenses[] = Expense::fromArray($e);
                } elseif ($e instanceof Expense) {
                    $c->expenses[] = $e;
                }
            }
        }

        return $c;
    }

    public function toArray(): array
    {
        $expenses = [];
        foreach ($this->expenses as $e) {
            if ($e instanceof Expense) {
                $expenses[] = $e->toArray();
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'expenses' => $expenses,
        ];
    }
}