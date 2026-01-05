<?php

class ShoppingList
{
    public int $id;
    public int $group_id;
    public string $name;
    public int $created_by_user_id;
    public DateTimeInterface $created_at;
    public DateTimeInterface $updated_at;
    public ?Group $group = null;
    public ?User $createdBy = null;
    /** @var ListItem[] */
    public array $items = [];

    public static function fromArray(array $data): self
    {
        $sl = new self();
        $sl->id = isset($data['id']) ? (int)$data['id'] : 0;
        $sl->group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
        $sl->name = $data['name'] ?? '';
        $sl->created_by_user_id = isset($data['created_by_user_id']) ? (int)$data['created_by_user_id'] : 0;

        if (isset($data['created_at'])) {
            try {
                $sl->created_at = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                $sl->created_at = new \DateTimeImmutable();
            }
        } else {
            $sl->created_at = new \DateTimeImmutable();
        }

        if (isset($data['updated_at'])) {
            try {
                $sl->updated_at = new \DateTimeImmutable($data['updated_at']);
            } catch (\Exception $e) {
                $sl->updated_at = new \DateTimeImmutable();
            }
        } else {
            $sl->updated_at = new \DateTimeImmutable();
        }

        if (isset($data['group']) && is_array($data['group'])) {
            $sl->group = Group::fromArray($data['group']);
        }
        if (isset($data['created_by']) && is_array($data['created_by'])) {
            $sl->createdBy = User::fromArray($data['created_by']);
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $it) {
                if (is_array($it)) {
                    $item = ListItem::fromArray($it);
                    if (!isset($item->list_id) || $item->list_id === 0) {
                        $item->list_id = $sl->id;
                    }
                    $sl->items[] = $item;
                } elseif ($it instanceof ListItem) {
                    $sl->items[] = $it;
                }
            }
        }

        return $sl;
    }

    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $it) {
            if ($it instanceof ListItem) {
                $items[] = $it->toArray();
            }
        }

        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'name' => $this->name,
            'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(DATE_ATOM) : null,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format(DATE_ATOM) : null,
            'group' => $this->group ? $this->group->toArray() : null,
            'created_by' => $this->createdBy ? $this->createdBy->toArray() : null,
            'items' => $items,
        ];
    }
}