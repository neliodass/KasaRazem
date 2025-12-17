<?php

class ListItem
{
    public int $id;
    public int $list_id;
    public string $name;
    public ?string $subtitle = null;
    public float $quantity = 1.0; // NUMERIC(10, 2)
    public string $unit = 'szt.';
    public bool $is_in_cart = false;
    public bool $is_purchased = false;
    public ?int $purchased_by_user_id = null;

    public ?ShoppingList $list = null;
    public ?User $purchasedBy = null;

    public static function fromArray(array $data): self
    {
        $it = new self();
        $it->id = isset($data['id']) ? (int)$data['id'] : 0;
        $it->list_id = isset($data['list_id']) ? (int)$data['list_id'] : 0;
        $it->name = $data['name'] ?? '';
        $it->subtitle = $data['subtitle'] ?? null;
        $it->quantity = isset($data['quantity']) ? (float)$data['quantity'] : 1.0;
        $it->unit = $data['unit'] ?? 'szt.';
        $it->is_in_cart = isset($data['is_in_cart']) ? (bool)$data['is_in_cart'] : false;
        $it->is_purchased = isset($data['is_purchased']) ? (bool)$data['is_purchased'] : false;
        $it->purchased_by_user_id = isset($data['purchased_by_user_id']) && $data['purchased_by_user_id'] !== null ? (int)$data['purchased_by_user_id'] : null;

        if (isset($data['list']) && is_array($data['list'])) {
            $it->list = ShoppingList::fromArray($data['list']);
        }
        if (isset($data['purchased_by']) && is_array($data['purchased_by'])) {
            $it->purchasedBy = User::fromArray($data['purchased_by']);
        }

        return $it;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'list_id' => $this->list_id,
            'name' => $this->name,
            'subtitle' => $this->subtitle,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'is_in_cart' => (bool)$this->is_in_cart,
            'is_purchased' => (bool)$this->is_purchased,
            'purchased_by_user_id' => $this->purchased_by_user_id,
            'list' => $this->list ? $this->list->toArray() : null,
            'purchased_by' => $this->purchasedBy ? $this->purchasedBy->toArray() : null,
        ];
    }
}
