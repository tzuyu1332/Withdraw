<?php

namespace App\Services\Withdraw;

interface WithdrawStrategyInterface
{
    public function withdraw($virtualAccount);

    public function handlePreWithdraw($virtualAccount);

    public function handlePostWithdraw();

}