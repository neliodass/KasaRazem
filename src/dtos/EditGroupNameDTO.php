<?php


class EditGroupNameDTO
{
    public int $id;
    public string $name;

    public static function fromPost(array $post_array)
    {
        $dto = new self();
        $dto->id = isset($post_array['group_id']) ? (int)$post_array['group_id'] : 0;
        $dto->name = $post_array['group_name'] ?? '';
        return $dto;
    }
}