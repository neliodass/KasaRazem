<?php

class ListItemOutputDTO
{
    public int $id;
    public string $name;
    public ?string $subtitle;
    public float $quantity;
    public string $unit;
    public bool $isInCart;
    public bool $isPurchased;

    public function __construct(ListItem $item)
    {
        $this->id = $item->id;
        $this->name = $item->name;
        $this->subtitle = $item->subtitle;
        $this->quantity = $item->quantity;
        $this->unit = $item->unit;
        $this->isInCart = $item->is_in_cart;
        $this->isPurchased = $item->is_purchased;
    }
}

