<?php


require_once "repository/GroupRepository.php";
require_once "src/dtos/GroupListDTO.php";
require_once "src/dtos/CreateGroupRequestDTO.php";

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
    public function getUsersInGroup(string $groupId): array
    {
        return $this->groupRepository->getUsersInGroup((int)$groupId);
    }

    public function createGroup(CreateGroupRequestDTO $dto): int
    {

        Auth::requireLogin();
        $userId = Auth::userId();
        if ($userId === null) {
            throw new RuntimeException("Użytkownik nie jest zalogowany.");
        }

        $newGroupId =  $this->groupRepository->createGroup($dto->name, $userId);
        if ($newGroupId != null) {
            return $newGroupId;
        }
        {
            throw new RuntimeException("Nie udało się utworzyć grupy.");
        }

    }

    public function getGroupsForUser(int $userId): array
    {
        return $this->groupRepository->getGroupsByUserId($userId);
    }
    public function getGroupsListDtoForUser(int $userId): array
    {
        $groupsData = $this->groupRepository->getGroupsByUserId($userId);
        $groupsDtos = [];
        foreach ($groupsData as $data) {
            $entity = $data['group'];
            $dto = new GroupListDTO();
            $dto->id = $entity->id;
            $dto->name = $entity->name;
            $dto->invite_id = $entity->invite_id;
            $dto->member_count = $data['member_count'];

            $groupsDtos[] = $dto;
        }
        return $groupsDtos;
    }
}