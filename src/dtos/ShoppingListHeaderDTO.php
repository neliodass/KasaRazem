<?php

class ShoppingListHeaderDTO
{
    public int $id;
    public string $name;
    public string $updatedAt;

    public function __construct(ShoppingList $list)
    {
        $this->id = $list->id;
        $this->name = $list->name;
        $this->updatedAt = $list->updated_at->format('Y-m-d H:i:s');
    }
}

