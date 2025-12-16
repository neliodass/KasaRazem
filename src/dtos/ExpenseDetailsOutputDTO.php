<?php



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
    /** @var ExpenseSplitOutputDTO[] */
    public array $splits;

    public function __construct(Expense $expense, string $icon, array $colors)
    {
        $this->id = $expense->id;
        $this->name = $expense->description;
        $this->amount = $expense->amount;
        $this->dateIncurred = $expense->date_incurred->format('Y-m-d');
        $this->payerName = $expense->paidBy
            ? $expense->paidBy->firstname . ' ' . $expense->paidBy->lastname
            : 'Unknown';
        $this->categoryName = $expense->category ? $expense->category->name : 'Inne';

        $this->icon = $icon;
        $this->iconBgColor = $colors['background'];
        $this->iconColor = $colors['icon'];

        $this->splits = [];
        foreach ($expense->splits as $split) {
            $this->splits[] = new ExpenseSplitOutputDTO($split);
        }
    }
}
