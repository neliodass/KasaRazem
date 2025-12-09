<?php

require_once "src/services/GroupService.php";
class BalanceController extends AppController
{
    private static $instance = null;
    private GroupService $groupService;
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
    }

    public function balance($groupId)
    {
        $this->render('moneyBalance',[
            "activeTab"=>"balance",
            "groupName"=>($this->groupService)->getGroupName((string)$groupId),
            "groupId"=>$groupId]);
    }

}