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
}