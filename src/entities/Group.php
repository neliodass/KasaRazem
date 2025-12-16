<?php


class Group
{
    public int $id;
    public string $name;
    public int $created_by_user_id;
    public string $invite_id; // (UUID jako string)
    public DateTimeInterface $created_at;
    public ?User $createdBy = null; //  Many-To-One (REFERENCES users(id))


    /** @var User[] */
    public array $members = [];
    /** @var Expense[] */
    public array $expenses = [];
    /** @var Settlement[] */
    public array $settlements = [];
    /** @var ShoppingList[] */
    public array $shoppingLists = [];
}