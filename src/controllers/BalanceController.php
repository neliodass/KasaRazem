<?php

namespace controllers;

class BalanceController extends AppController
{
    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function groupBalance()
    {

    }

}