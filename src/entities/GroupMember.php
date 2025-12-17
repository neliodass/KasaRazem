<?php

class GroupMember
{
    public int $group_id;
    public int $user_id;
    public DateTimeInterface $joined_at;

    public ?Group $group = null;
    public ?User $user = null;

    public static function fromArray(array $data): self
    {
        $gm = new self();
        $gm->group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
        $gm->user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        if (isset($data['joined_at'])) {
            try {
                $gm->joined_at = new \DateTimeImmutable($data['joined_at']);
            } catch (\Exception $e) {
                $gm->joined_at = new \DateTimeImmutable();
            }
        } else {
            $gm->joined_at = new \DateTimeImmutable();
        }

        if (isset($data['group']) && is_array($data['group'])) {
            $gm->group = Group::fromArray($data['group']);
        }
        if (isset($data['user']) && is_array($data['user'])) {
            $gm->user = User::fromArray($data['user']);
        }

        return $gm;
    }

    public function toArray(): array
    {
        return [
            'group_id' => $this->group_id,
            'user_id' => $this->user_id,
            'joined_at' => $this->joined_at instanceof \DateTimeInterface ? $this->joined_at->format(DATE_ATOM) : null,
            'group' => $this->group ? $this->group->toArray() : null,
            'user' => $this->user ? $this->user->toArray() : null,
        ];
    }
}