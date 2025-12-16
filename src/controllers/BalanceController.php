<?php

require_once "src/services/BalanceService.php";
require_once "src/services/GroupService.php";
require_once "src/services/AuthService.php";
require_once "repository/SettlementRepository.php";
require_once "src/dtos/SettleDebtRequestDTO.php";

class BalanceController extends AppController
{
    private static $instance = null;
    private GroupService $groupService;
    private BalanceService $balanceService;
    private AuthService $authService;
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
        $this->authService = AuthService::getInstance();
        $this->settlementRepository = SettlementRepository::getInstance();
    }

    public function balance($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);

        $currentUserId = (int)Auth::userId();
        $groupName = $this->groupService->getGroupName((string)$groupId);

        $balanceDTO = $this->balanceService->getBalanceForView(
            (int)$groupId,
            $currentUserId,
            $groupName
        );

        $this->render('moneyBalance', [
            'activeTab' => 'balance',
            'groupName' => $balanceDTO->groupName,
            'groupId' => $balanceDTO->groupId,
            'balance' => $balanceDTO->balanceItems,
            'currentUserNetBalance' => $balanceDTO->currentUserNetBalance,
            'currentUserBalanceEmoji' => $balanceDTO->currentUserBalanceEmoji,
            'inviteId' => $this->groupService->getGroupInviteId((string)$groupId)
        ]);
    }

    public function settleDetails($groupId)
    {
        $this->authService->verifyUserInGroup($groupId);

        $currentUserId = (int)Auth::userId();
        $groupName = $this->groupService->getGroupName((string)$groupId);

        $settlementsDTO = $this->balanceService->getSettlementsForView(
            (int)$groupId,
            $currentUserId,
            $groupName
        );

        $this->render('settlementsDetails', [
            'activeTab' => 'balance',
            'groupName' => $settlementsDTO->groupName,
            'groupId' => $settlementsDTO->groupId,
            'settlements' => $settlementsDTO->settlements,
            'inviteId' => $this->groupService->getGroupInviteId((string)$groupId)
        ]);
    }

    public function settleDebt($groupId)
    {
        if (!$this->isPost()) {
            $this->redirect("/groups/$groupId/balance");
            return;
        }

        $this->authService->verifyUserInGroup($groupId);

        $currentUserId = (int)Auth::userId();
        $dto = SettleDebtRequestDTO::fromPost((int)$groupId);

        if (!$dto->validate($currentUserId)) {
            $this->redirect("/groups/$groupId/settlements");
            return;
        }

        $this->settlementRepository->addSettlement(
            $dto->payerId,
            $dto->payeeId,
            $dto->amount,
            $dto->groupId,
            date('Y-m-d')
        );

        $this->redirect("/groups/$groupId/settlements");
    }
}