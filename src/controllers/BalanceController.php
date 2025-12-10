<?php

require_once "src/services/BalanceService.php";
require_once "src/services/GroupService.php";

class BalanceController extends AppController
{
    private static $instance = null;
    private GroupService $groupService;
    private BalanceService $balanceService;

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

}