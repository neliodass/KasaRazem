<?php


require_once "repository/GroupRepository.php";
class GroupService
{
    private static $instance = null;
    private $groupRepository;
    private function __construct()
    {
        $this->groupRepository = GroupRepository::getInstance();
    }
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getGroupName(string $groupId): ?string
    {
        if($group = $this->groupRepository->getGroupById($groupId)) {
            return $group['name'];
        }
        return "Grupa";
    }
}