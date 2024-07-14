<?php

namespace App\Services\Withdraw;

class WithdrawService
{
    const WITHDRAW_MIN_LIMIT_INCLUSIVE = 100;

    public function withdraw(int $memberId): void
    {
        $virtualAccounts = $this->getVirtualAccounts($memberId);

        $this->handlePreWithdraw($virtualAccounts);
        $this->handleWithdraw($virtualAccounts);
        $this->handlePostWithdraw();
    }

    private function handlePostWithdraw()
    {
        $this->sendSuccessfulEDM();
    }

    private function sendSuccessfulEDM()
    {
        $mailData = [
            // Add email data...
        ];

        Mail::send($mailData);
    }

    private function handleWithdraw($virtualAccounts): void
    {
        $context = new WithdrawContext();
        foreach ($virtualAccounts as $virtualAccount) {
            $strategy = $this->getStrategy($virtualAccount->account_type);
            $context->setStrategy($strategy);
            $context->execute($virtualAccount);
        }
    }

    private function getStrategy($accountType): WithdrawStrategyInterface
    {
        switch ($accountType) {
            case VirtualAccountsConstants::ACCOUNT_TYPE_CATHAY:
                $withdrawResult = new WithdrawResult();
                return new CathayBankWithdraw($withdrawResult);
            // Add other types...
            default:
                throw new CustomException('Error Code');
        }
    }

    private function handlePreWithdraw($virtualAccounts): void
    {
        if ($virtualAccounts->isEmpty()) {
            throw new CustomException('Error Code');
        }

        if ($virtualAccounts->sum('balance') < self::WITHDRAW_MIN_LIMIT_INCLUSIVE) {
            throw new CustomException('Error Code');
        }
    }

    private function getVirtualAccounts($memberId): Collection
    {
        return VirtualAccounts::where('member_id', $memberId)
            // Add other conditions...
            ->get();
    }
}