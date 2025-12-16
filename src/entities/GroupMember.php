<?php

class GroupMember
{
    public int $group_id;
    public int $user_id;
    public DateTimeInterface $joined_at;

    public ?Group $group = null; // Many-To-One (REFERENCES groups(id))
    public ?User $user = null; // Many-To-One (REFERENCES users(id))
}