<?php
require_once "repository/Repository.php";
class GroupRepository extends Repository {
    private static $instance;
    public static function getInstance(): GroupRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct()
    {
        parent::__construct();
    }

    public function getGroupsByUserId(string $userId): ?array
    {
        $query = $this->conn->prepare(
            'SELECT g.*, member_counts.member_count 
FROM groups g
JOIN (
    SELECT group_id, COUNT(user_id) as member_count
    FROM group_members
    GROUP BY group_id
) AS member_counts ON g.id = member_counts.group_id
JOIN group_members gm_filter ON g.id = gm_filter.group_id
WHERE gm_filter.user_id = :userId;'
        );
        $query->bindParam(':userId', $userId, PDO::PARAM_STR);
        $query->execute();
        $groups = $query->fetchAll(PDO::FETCH_ASSOC);

        return $groups;
    }
    public function getGroupIdByInviteCode(string $inviteCode): ?int
    {
        $query = $this->conn->prepare(
            'SELECT id FROM groups WHERE invite_id = :inviteCode'
        );

        $query->bindParam(':inviteCode', $inviteCode, PDO::PARAM_STR);
        $query->execute();

        $id = $query->fetchColumn();

        return $id !== false ? (int)$id : null;
    }
    public function isUserInGroup(int $groupId, int $userId): bool
    {
        $query = $this->conn->prepare(
            'SELECT COUNT(*) FROM group_members WHERE group_id = :groupId AND user_id = :userId'
        );

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $query->execute();
        return (bool)$query->fetchColumn();
    }
    public function addUserToGroup(int $groupId, int $userId): bool
    {
        $query = $this->conn->prepare(
            'INSERT INTO group_members (group_id, user_id) 
         VALUES (:groupId, :userId)'
        );

        $query->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $query->bindParam(':userId', $userId, PDO::PARAM_INT);

        return $query->execute();
    }

}