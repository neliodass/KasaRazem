<?php

class ExpenseSummaryOutputDto
{
    public int $id;
    public string $name;
    public float $amount;
    public string $paidBy;
    public string $dateIncurred;
    public string $icon;
    public string $iconBgColor;
    public string $iconColor;
    public ?int $categoryId = null;
}

