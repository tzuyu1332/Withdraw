<?php

namespace App\Services\Withdraw;

class WithdrawContext
{
    private WithdrawStrategyInterface $withdrawStrategy;

    public function execute($virtualAccount): void
    {
        $this->withdrawStrategy->handlePreWithdraw($virtualAccount);
        $this->withdrawStrategy->withdraw($virtualAccount);
        $this->withdrawStrategy->handlePostWithdraw();
    }

    public function setStrategy(WithdrawStrategyInterface $withdrawStrategy): void
    {
        $this->withdrawStrategy = $withdrawStrategy;
    }
}