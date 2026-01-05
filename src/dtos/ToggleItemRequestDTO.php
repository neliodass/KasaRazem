<?php

class ToggleItemRequestDTO
{
    public bool $isPurchased;

    public function __construct(array $data)
    {
        $this->isPurchased = (bool)($data['isPurchased'] ?? false);
    }

    public static function fromJson(): self
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return new self($data ?? []);
    }
}
