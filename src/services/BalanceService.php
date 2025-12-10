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

    public function getBalanceSummary(string $groupId, string $currentUserId): array
    {
        $groupId = (int)$groupId;
        $currentUserId = (int)$currentUserId;
        $debtData = $this->expenseRepository->getDebtDataByGroupId($groupId);
        $users = $this->groupRepository->getUsersInGroup($groupId);
        $owed = $debtData['owed'];
        $paid = $debtData['paid'];
        $settlements = $this->expenseRepository->getSettlementsByGroupId($groupId);
        foreach ($settlements as $settlement) {
            $payerId = (int)$settlement['payer_user_id'];
            $payeeId = (int)$settlement['payee_user_id'];
            $amount = (float)$settlement['amount'];

            $owed[$payerId] = ((float)$owed[$payerId] ?? 0.0) - $amount;
            $paid[$payeeId] = ((float)$paid[$payeeId] ?? 0.0) - $amount;
        }
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
                'netBalance' => round($netBalance, 3),
                'isCurrentUser' => $userId === $currentUserId
            ];

            if ($userId === $currentUserId) {
                $currentUserNetBalance = round($netBalance, 3);
            }
        }

        usort($balance, function ($a, $b) {
            return $a['netBalance'] <=> $b['netBalance'];
        });
        return [
            'balance' => $balance,
            'currentUserNetBalance' => $currentUserNetBalance,
        ];
    }
    public function getBalanceEmoji(float $amount): string
    {
        if ($amount > 0) {
            return "🤑";
        } elseif ($amount < 0) {
            return "💸";
        } else {
            return "🤝";
        }
    }
    public function calculateSettlements(array $balance): array
    {
        $settlements = [];
        $netBalances = [];
        foreach ($balance as $entry) {
            $netBalances[$entry['userId']] = $entry['netBalance'];
        }
        $debtors = array_filter($netBalances, fn($balance) => $balance < 0);
        $creditors = array_filter($netBalances, fn($balance) => $balance > 0);

        while(!empty($debtors) && !empty($creditors)) {
            $debtorId = array_key_first($debtors);
            $debt = abs($debtors[$debtorId]);
            $creditorId = array_key_first($creditors);
            $credit = $creditors[$creditorId];

            $settlementAmount = min($debt, $credit);
            $settlementAmount = round($settlementAmount, 2);

            if ($settlementAmount > 0.00) {
                $settlements[] = [
                    'from' => (int)$debtorId,
                    'to' => (int)$creditorId,
                    'amount' => $settlementAmount,
                ];
            }
            $debtors[$debtorId] += $settlementAmount;
            $creditors[$creditorId] -= $settlementAmount;

            if (abs($debtors[$debtorId]) < 0.01) {
                unset($debtors[$debtorId]);
            }
            if ($creditors[$creditorId] < 0.01) {
                unset($creditors[$creditorId]);
            }
        }


        return $settlements;
    }

}