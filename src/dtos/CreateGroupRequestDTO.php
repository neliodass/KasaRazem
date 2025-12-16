<?php


class CreateGroupRequestDTO
{
    public readonly string $name;
    private const MAX_NAME_LENGTH = 100;
    private const MIN_NAME_LENGTH = 1;

    public static function fromPost(array $postData): self
    {
        if (!isset($postData['group_name'])) {
            throw new InvalidArgumentException("Nazwa grupy jest wymagana.");
        }

        $groupName = trim($postData['group_name']);

        self::validateGroupName($groupName);

        $dto = new self($groupName);

        return $dto;
    }

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    private static function validateGroupName(string $name): void
    {
        $length = strlen($name);

        if ($length < self::MIN_NAME_LENGTH || $length > self::MAX_NAME_LENGTH) {
            throw new InvalidArgumentException(
                "Nazwa grupy musi mieć od " . self::MIN_NAME_LENGTH .
                " do " . self::MAX_NAME_LENGTH . " znaków."
            );
        }

    }
}