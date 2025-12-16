<?php
class ValidationException extends \Exception {}
class GroupJoinByCodeRequestDto
{
    public $code;

    public static function fromPost(): self
    {
        if (!isset($_POST['code'])) {
            throw new ValidationException('Pole "code" jest wymagane.');
        }

        $instance = new self();
        $code = trim($_POST['code']);

        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        if (!preg_match($uuidPattern, $code)) {
            throw new ValidationException('Nieprawidłowy kod zaproszenia.');
        }

        $instance->code = $code;

        return $instance;
    }
}