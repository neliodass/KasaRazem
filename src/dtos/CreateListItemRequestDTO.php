<?php

class CreateListItemRequestDTO
{
    public string $name;
    public string $subtitle;
    public float $quantity;
    public string $unit;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->subtitle = $data['subtitle'] ?? '';
        $this->quantity = (float)($data['quantity'] ?? 1.0);
        $this->unit = $data['unit'] ?? 'szt.';
    }

    public static function fromJson(): self
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return new self($data ?? []);
    }

    public static function fromPost(): self
    {
        return new self($_POST);
    }
}

