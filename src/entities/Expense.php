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
}