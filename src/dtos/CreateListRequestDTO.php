<?php

class CreateListRequestDTO
{
    public string $name;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
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

