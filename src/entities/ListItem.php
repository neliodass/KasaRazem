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
}
