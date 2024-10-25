<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Expense;
use Symfony\Contracts\EventDispatcher\Event;

class ExpenseAddEvent extends Event
{
    public const NAME = 'expense.created';

    private Expense $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    public function getExpense(): Expense
    {
        return $this->expense;
    }
}