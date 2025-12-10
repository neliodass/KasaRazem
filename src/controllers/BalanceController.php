<?php

require_once "src/services/BalanceService.php";
require_once "src/services/GroupService.php";
require_once "repository/SettlementRepository.php";

class BalanceController extends AppController
{
    private static $instance = null;
    private GroupService $groupService;
    private BalanceService $balanceService;
    private SettlementRepository $settlementRepository;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->groupService = GroupService::getInstance();
        $this->balanceService = BalanceService::getInstance();
        $this->settlementRepository = SettlementRepository::getInstance();
    }

    public function balance($groupId)
    {
        Auth::requireLogin();
        $currentUserId = (string)Auth::userId();
        $balanceData = $this->balanceService->getBalanceSummary((string)$groupId, (string)$currentUserId);
        $this->render('moneyBalance', [
            "activeTab" => "balance",
            "groupName" => ($this->groupService)->getGroupName((string)$groupId),
            "groupId" => $groupId,
            "balance" => $balanceData['balance'],
            "currentUserNetBalance" => $balanceData['currentUserNetBalance'],
            "currentUserBalanceEmoji"=> $this->balanceService->getBalanceEmoji($balanceData['currentUserNetBalance'])
        ]);
        exit();
    }

    public function settleDetails($groupId)
    {
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $currentUserId = (int)Auth::userId();
        $balanceData = $this->balanceService->getBalanceSummary((string)$groupId, (string)$currentUserId);
        $settlements = $this->balanceService->calculateSettlements($balanceData['balance']);
        $usersInGroup = $this->groupService->getUsersInGroup($groupId);
        $users = array_column($usersInGroup, 'firstname', 'id');
        $settlementsNamed = [];
        foreach ($settlements as $settlement) {
            $settlement['from_name'] = $users[$settlement['from']];
            $settlement['to_name'] = $users[$settlement['to']];
            $settlementsNamed[] = $settlement;
        }

        $this->render('settlementsDetails', [
            "activeTab" => "balance",
            "groupName" => ($this->groupService)->getGroupName((string)$groupId),
            "groupId" => $groupId,
            "settlements" => $settlementsNamed,
            "usersInGroup" => $usersInGroup
        ]);
        exit();
    }
    public function settleDebt($groupId)
    {
        if(!$this->isPost()) {
            header("Location: /groups/$groupId/balance");
            exit();
        }
        Auth::requireLogin();
        $groupId = (int)$groupId;
        $currentUserId = (int)Auth::userId();
        $payerId = (int)$_POST['payer_id']??0;
        $payeeId = (int)$_POST['payee_id']??0;
        $amount = (float)$_POST['amount']??0.0;
        if($payerId !== $currentUserId && $payeeId !== $currentUserId) {
            header("Location: /groups/$groupId/balance");
            exit();
        }
        if($amount >0 && $payerId !== $payeeId) {
            $this->settlementRepository->addSettlement(
                $payerId,
                $payeeId,
                $amount,
                $groupId,
                date('Y-m-d')
            );
        }
        $this->redirect('/groups/' . $groupId . '/settlements');
        exit();
    }


}