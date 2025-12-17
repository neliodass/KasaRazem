<?php

require_once "repository/ExpenseRepository.php";
require_once "repository/UserRepository.php";
require_once "repository/GroupRepository.php";
require_once "src/dtos/UserBalanceItemDTO.php";
require_once "src/dtos/BalanceSummaryOutputDTO.php";
require_once "src/dtos/SettlementItemDTO.php";
require_once "src/dtos/SettlementsDetailsOutputDTO.php";

class BalanceService
{
    private ExpenseRepository $expenseRepository;
    private GroupRepository $groupRepository;
    private static $instance = null;

    private function __construct()
    {
        $this->expenseRepository = ExpenseRepository::getInstance();
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

    public function getBalanceForView(int $groupId, int $currentUserId, string $groupName): BalanceSummaryOutputDTO
    {
        $debtData = $this->expenseRepository->getDebtDataByGroupId($groupId);
        $users = $this->groupRepository->getUsersByGroupId($groupId);
        $owed = $debtData['owed'];
        $paid = $debtData['paid'];
        $settlements = $this->expenseRepository->getSettlementsByGroupId($groupId);
        foreach ($settlements as $settlement) {
            $payerId = $settlement->payer_user_id;
            $payeeId = $settlement->payee_user_id;
            $amount = $settlement->amount;

            $owed[$payerId] = ((float)($owed[$payerId] ?? 0.0)) - $amount;
            $paid[$payeeId] = ((float)($paid[$payeeId] ?? 0.0)) - $amount;
        }

        $balanceItems = [];
        $currentUserNetBalance = 0.0;

        foreach ($users as $user) {
            $userId = $user->id;
            $userOwed = $owed[$userId] ?? 0.0;
            $userPaid = $paid[$userId] ?? 0.0;
            $netBalance = round($userPaid - $userOwed, 2);

            $balanceItems[] = new UserBalanceItemDTO(
                $userId,
                $user->firstname . ' ' . $user->lastname,
                $netBalance,
                $userId === $currentUserId,
                $user->profile_picture
            );

            if ($userId === $currentUserId) {
                $currentUserNetBalance = $netBalance;
            }
        }

        usort($balanceItems, function ($a, $b) {
            return $a->netBalance <=> $b->netBalance;
        });

        $emoji = $this->getBalanceEmoji($currentUserNetBalance);

        return new BalanceSummaryOutputDTO(
            $balanceItems,
            $currentUserNetBalance,
            $emoji,
            $groupName,
            $groupId
        );
    }

    public function getBalanceSummary(string $groupId, string $currentUserId): array
    {
        $groupId = (int)$groupId;
        $currentUserId = (int)$currentUserId;
        $debtData = $this->expenseRepository->getDebtDataByGroupId($groupId);
        $users = $this->groupRepository->getUsersByGroupId($groupId);
        $owed = $debtData['owed'];
        $paid = $debtData['paid'];
        $settlements = $this->expenseRepository->getSettlementsByGroupId($groupId);
        foreach ($settlements as $settlement) {
            $payerId = $settlement->payer_user_id;
            $payeeId = $settlement->payee_user_id;
            $amount = $settlement->amount;

            $owed[$payerId] = ((float)($owed[$payerId] ?? 0.0)) - $amount;
            $paid[$payeeId] = ((float)($paid[$payeeId] ?? 0.0)) - $amount;
        }

        $balance = [];
        $currentUserNetBalance = 0.0;
        foreach ($users as $user) {
            $userId = $user->id;
            $userOwed = $owed[$userId] ?? 0.0;
            $userPaid = $paid[$userId] ?? 0.0;
            $netBalance = $userPaid - $userOwed;

            $balance[] = [
                'userId' => $userId,
                'userName' => $user->firstname . ' ' . $user->lastname,
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

    public function getSettlementsForView(int $groupId, int $currentUserId, string $groupName): SettlementsDetailsOutputDTO
    {
        // Pobierz dane balansu
        $balanceData = $this->getBalanceSummary((string)$groupId, (string)$currentUserId);

        // Oblicz minimalne rozliczenia
        $settlementsRaw = $this->calculateSettlements($balanceData['balance']);

        // Pobierz użytkowników grupy
        $users = $this->groupRepository->getUsersByGroupId($groupId);

        // Utwórz mapę userId => User
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->id] = $user;
        }

        // Konwertuj na DTO
        $settlementDTOs = [];
        foreach ($settlementsRaw as $settlement) {
            $fromUser = $userMap[$settlement['from']] ?? null;
            $toUser = $userMap[$settlement['to']] ?? null;

            if ($fromUser && $toUser) {
                $settlementDTOs[] = new SettlementItemDTO(
                    $settlement['from'],
                    $fromUser->firstname . ' ' . $fromUser->lastname,
                    $settlement['to'],
                    $toUser->firstname . ' ' . $toUser->lastname,
                    $settlement['amount']
                );
            }
        }

        return new SettlementsDetailsOutputDTO($settlementDTOs, $groupName, $groupId);
    }

}