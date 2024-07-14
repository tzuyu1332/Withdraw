<?php

namespace App\Services\Withdraw\Strategies;

class CathayBankWithdraw implements WithdrawStrategyInterface
{
    const DEFAULT_FAILURE_MESSAGE = '國泰信託提領失敗';
    const WITHDRAW_MIN_LIMIT_EXCLUSIVE = 15;

    public WithdrawResult $withdrawResult;
    private $bankAccount;

    public function __construct(WithdrawResult $withdrawResult)
    {
        $this->withdrawResult = $withdrawResult;
    }

    public function withdraw($virtualAccount)
    {
        if ($this->withdrawResult->isFailed()) {
            return;
        }

        DB::beginTransaction();

        try {
            // Update balance...

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->withdrawResult->setFailure($e->getMessage(), WithdrawResult::SHOW_FLAG_DEVELOPER);
        }
    }

    public function handlePreWithdraw($virtualAccount): void
    {
        try {
            $this->checkBalance($virtualAccount);
            $this->bankAccount = $this->getBankAccount($virtualAccount->member_id);
        } catch (Exception $e) {
            $this->withdrawResult->setFailure($e->getMessage(), WithdrawResult::SHOW_FLAG_DEVELOPER);
        }
    }

    private function getBankAccount($memberId)
    {
        $data = BankAccounts::where('member_id', $memberId)
            // Add other condition...
            ->first();

        if (is_null($data)) {
            $this->withdrawResult->setFailure(self::DEFAULT_FAILURE_MESSAGE, WithdrawResult::SHOW_FLAG_END_USER);
            throw new Exception('查無此會員的收款帳戶[' . $memberId . ']');
        }

        return $data;
    }

    private function checkBalance($virtualAccount): void
    {
        if ($virtualAccount->balance <= self::WITHDRAW_MIN_LIMIT_EXCLUSIVE) {
            $this->withdrawResult->setFailure('國泰信託餘額不足', WithdrawResult::SHOW_FLAG_END_USER);
            $message = '虛擬帳戶餘額不足' . self::WITHDRAW_MIN_LIMIT_EXCLUSIVE . '[' . $virtualAccount->member_id . '][account_type=' . VirtualAccountsConstants::ACCOUNT_TYPE_CATHAY . ']';
            throw new Exception($message);
        }
    }

    public function handlePostWithdraw()
    {
        try {
            if ($this->withdrawResult->isSuccessful()) {
                $this->handleSuccessful();
            } else {
                $this->handleFailed();
            }
        } catch (Exception $e) {
            $this->withdrawResult->setFailure($e->getMessage(), WithdrawResult::SHOW_FLAG_DEVELOPER);
        }
    }

    private function handleFailed(): void
    {
        // Insert withdraw log...
        // Unfrozen account...
        // Send notify to channel...
    }

    private function handleSuccessful(): void
    {
        // Insert withdraw log...
        // Unfrozen account...
        // Insert transaction log...
    }
}