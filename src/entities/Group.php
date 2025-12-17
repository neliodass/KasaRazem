<?php


class Group
{
    public int $id;
    public string $name;
    public int $created_by_user_id;
    public string $invite_id;
    public DateTimeInterface $created_at;
    public ?User $createdBy = null;

    public array $members = [];
    public array $expenses = [];
    public array $settlements = [];
    public array $shoppingLists = [];

    public static function fromArray(array $data): self
    {
        $group = new self();
        $group->id = isset($data['id']) ? (int)$data['id'] : 0;
        $group->name = $data['name'] ?? '';
        $group->created_by_user_id = isset($data['created_by_user_id']) ? (int)$data['created_by_user_id'] : 0;
        $group->invite_id = $data['invite_id'] ?? '';

        if (isset($data['created_at'])) {
            try {
                $group->created_at = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                $group->created_at = new \DateTimeImmutable();
            }
        } else {
            $group->created_at = new \DateTimeImmutable();
        }

        if (isset($data['created_by']) && is_array($data['created_by'])) {
            $group->createdBy = User::fromArray($data['created_by']);
        } else {
            $group->createdBy = null;
        }

        if (isset($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $m) {
                if (is_array($m)) {
                    $group->members[] = User::fromArray($m);
                } elseif ($m instanceof User) {
                    $group->members[] = $m;
                }
            }
        }

        return $group;
    }

    public function toArray(): array
    {
        $members = [];
        foreach ($this->members as $m) {
            if ($m instanceof User) {
                $members[] = $m->toArray();
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_by_user_id' => $this->created_by_user_id,
            'invite_id' => $this->invite_id,
            'created_at' => $this->created_at instanceof \DateTimeInterface ? $this->created_at->format(DATE_ATOM) : null,
            'created_by' => $this->createdBy ? $this->createdBy->toArray() : null,
            'members' => $members,];
}};