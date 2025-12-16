<?php

class ShoppingList
{
    public int $id;
    public int $group_id;
    public string $name;
    public int $created_by_user_id;
    public DateTimeInterface $created_at;
    public DateTimeInterface $updated_at;
    public ?Group $group = null;
    public ?User $createdBy = null;
    /** @var ListItem[] */
    public array $items = [];
}