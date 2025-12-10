<?php

require_once "repository/ExpenseRepository.php";
require_once "repository/UserRepository.php";
require_once "repository/GroupRepository.php";

class BalanceService
{
    private ExpenseRepository $expenseRepository;
    private UserRepository $userRepository;
    private GroupRepository $groupRepository;
    private static $instance = null;
    private function __construct()
    {
        $this->expenseRepository = ExpenseRepository::getInstance();
        $this->userRepository = UserRepository::getInstance();
        $this->groupRepository = GroupRepository::getInstance();
    }
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getSettlementForGroup(string $groupId): array
    {
        return [];
    }
    public function getBalanceSummary(string $groupId,string $currentUserId): array
    {
        $groupId = (int)$groupId;
        $currentUserId = (int)$currentUserId;
        $debtData = $this->expenseRepository->getDebtDataByGroupId($groupId);
        $users = $this->groupRepository->getUsersInGroup($groupId);
        $owed = $debtData['owed'];
        $paid = $debtData['paid'];

        $balance = [];
        $currentUserNetBalance = 0.0;
        foreach ($users as $user) {
            $userId = (int)$user['id'];
            $userOwed = $owed[$userId] ?? 0.0;
            $userPaid = $paid[$userId] ?? 0.0;
            $netBalance = $userPaid - $userOwed;

            $balance[] = [
                'userId' => $userId,
                'userName' => $user['firstname'] . ' ' . $user['lastname'],
                'netBalance' => round($netBalance, 2),
                'isCurrentUser' => $userId === $currentUserId
            ];

            if ($userId === $currentUserId) {
                $currentUserNetBalance = round($netBalance,2);
            }}

            usort($balance, function ($a, $b) {
                return $a['netBalance'] <=> $b['netBalance'];
            });
            return [
                'balance' => $balance,
                'currentUserNetBalance' => $currentUserNetBalance,
            ];
        }

}