<?php

class Settlement
{
    public int $id;
    public int $group_id;
    public int $payer_user_id; // Dłużnik
    public int $payee_user_id; // Wierzyciel
    public float $amount; // NUMERIC(10, 2)
    public DateTimeInterface $date_settled;

    public ?Group $group = null;
    public ?User $payer = null;
    public ?User $payee = null;

    public static function fromArray(array $data): self
    {
        $s = new self();
        $s->id = isset($data['id']) ? (int)$data['id'] : 0;
        $s->group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
        $s->payer_user_id = isset($data['payer_user_id']) ? (int)$data['payer_user_id'] : 0;
        $s->payee_user_id = isset($data['payee_user_id']) ? (int)$data['payee_user_id'] : 0;
        $s->amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;

        if (isset($data['date_settled'])) {
            try {
                $s->date_settled = new \DateTimeImmutable($data['date_settled']);
            } catch (\Exception $e) {
                $s->date_settled = new \DateTimeImmutable();
            }
        } else {
            $s->date_settled = new \DateTimeImmutable();
        }

        if (isset($data['group']) && is_array($data['group'])) {
            $s->group = Group::fromArray($data['group']);
        }
        if (isset($data['payer']) && is_array($data['payer'])) {
            $s->payer = User::fromArray($data['payer']);
        }
        if (isset($data['payee']) && is_array($data['payee'])) {
            $s->payee = User::fromArray($data['payee']);
        }

        return $s;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'payer_user_id' => $this->payer_user_id,
            'payee_user_id' => $this->payee_user_id,
            'amount' => $this->amount,
            'date_settled' => $this->date_settled instanceof \DateTimeInterface ? $this->date_settled->format(DATE_ATOM) : null,
            'group' => $this->group ? $this->group->toArray() : null,
            'payer' => $this->payer ? $this->payer->toArray() : null,
            'payee' => $this->payee ? $this->payee->toArray() : null,
        ];
    }
}