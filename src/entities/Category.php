<?php


class Category
{
    public int $id;
    public string $name;
    /** @var Expense[] */
    public array $expenses = [];
}