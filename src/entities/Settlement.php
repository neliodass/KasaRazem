<?php

class Settlement
{
    public int $id;
    public int $group_id;
    public int $payer_user_id; // Dłużnik
    public int $payee_user_id; // Wierzyciel
    public float $amount; // NUMERIC(10, 2)
    public DateTimeInterface $date_settled;

    public ?Group $group = null;
    public ?User $payer = null;
    public ?User $payee = null;
}