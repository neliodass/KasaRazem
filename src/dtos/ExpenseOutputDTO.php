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
class ExpenseDetailsOutputDTO
{
    public int $id;
    public string $name;
    public float $amount;
    public string $dateIncurred;
    public string $payerName;
    public string $categoryName;
    public string $icon;
    public string $iconBgColor;
    public string $iconColor;
    public array $splits;

    public function __construct(array $data, string $icon, array $colors)
    {
        $this->id = (int)$data['id'];
        $this->name = $data['name'];
        $this->amount = (float)$data['amount'];
        $this->dateIncurred = $data['date_incurred'];
        $this->payerName = $data['payer_firstname'] . ' ' . $data['payer_lastname'];
        $this->categoryName = $data['category_name'] ?? 'Inne';

        $this->icon = $icon;
        $this->iconBgColor = $colors['background'];
        $this->iconColor = $colors['icon'];

        $this->splits = $data['splits'] ?? [];
    }
}