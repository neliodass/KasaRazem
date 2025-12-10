<?php

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

    public function addSettlement(int $fromUserId, int $toUserId, float $amount, int $groupId, string $date): ?int
    {
        $query = $this->conn->prepare(
            'INSERT INTO settlements (group_id,payer_user_id,payee_user_id, amount, date_settled) 
                 VALUES (:groupId,:fromUserId, :toUserId, :amount,  :date)
                 RETURNING id'
        );

        $query->bindParam(':fromUserId', $fromUserId, PDO::PARAM_INT);
        $query->bindParam(':toUserId', $toUserId, PDO::PARAM_INT);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':date', $date);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['id'] : null;
    }

}