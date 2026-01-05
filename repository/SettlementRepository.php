<?php

require_once 'src/entities/Settlement.php';

class SettlementRepository extends Repository
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addSettlement(Settlement $settlement): ?Settlement
    {
        $query = $this->conn->prepare(
            'INSERT INTO settlements (group_id, payer_user_id, payee_user_id, amount, date_settled) 
                 VALUES (:groupId, :payerUserId, :payeeUserId, :amount, :dateSettled)
                 RETURNING *'
        );

        $groupId = $settlement->group_id;
        $payerUserId = $settlement->payer_user_id;
        $payeeUserId = $settlement->payee_user_id;
        $amount = $settlement->amount;
        $dateSettled = $settlement->date_settled instanceof \DateTimeInterface
            ? $settlement->date_settled->format('Y-m-d')
            : (new \DateTimeImmutable())->format('Y-m-d');

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':payerUserId', $payerUserId, PDO::PARAM_INT);
        $query->bindParam(':payeeUserId', $payeeUserId, PDO::PARAM_INT);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':dateSettled', $dateSettled);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return Settlement::fromArray($result);
        }
        return null;
    }

}