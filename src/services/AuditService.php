<?php

require_once 'core/Database.php';

class AuditService
{
    private Database $database;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->database = Database::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log(string $eventType, ?string $userEmail = null, array $additionalData = []): void
    {
        $ipAddress = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $additionalDataJson = !empty($additionalData) ? json_encode($additionalData) : null;

        $stmt = $this->database->connect()->prepare('
            INSERT INTO audit_logs (event_type, user_email, ip_address, user_agent, additional_data)
            VALUES (:event_type, :user_email, :ip_address, :user_agent, :additional_data)
        ');

        $stmt->execute([
            ':event_type' => $eventType,
            ':user_email' => $userEmail,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':additional_data' => $additionalDataJson
        ]);
    }

    private function getClientIp(): ?string
    {
        $ipAddress = null;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = trim($ipList[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        return $ipAddress;
    }

    public function getLogs(
        ?string $eventType = null,
        ?string $userEmail = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        $query = 'SELECT * FROM audit_logs WHERE 1=1';
        $params = [];

        if ($eventType !== null) {
            $query .= ' AND event_type = :event_type';
            $params[':event_type'] = $eventType;
        }

        if ($userEmail !== null) {
            $query .= ' AND user_email = :user_email';
            $params[':user_email'] = $userEmail;
        }

        $query .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->database->connect()->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
