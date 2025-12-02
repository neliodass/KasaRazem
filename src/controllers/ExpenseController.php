<?php

class ExpenseController extends AppController
{
    private static $instance;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function addExpense()
    {
        $this->render("addExpense");
    }
}