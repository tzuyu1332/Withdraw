<?php

namespace App\Services\Withdraw;

class WithdrawResult
{
    private array $messageList;
    private int $manualStatus;

    /* 狀態 */
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;

    /* 訊息呈現的對象 */
    const SHOW_FLAG_ALL = 1;
    const SHOW_FLAG_DEVELOPER = 2;
    const SHOW_FLAG_END_USER = 3;

    public function __construct(int $status = self::STATUS_SUCCESS)
    {
        $this->setStatus($status);
        $this->clear();
    }

    public function clear(): void
    {
        $this->messageList = [];
    }

    public function setStatus(int $status): void
    {
        $this->manualStatus = $status;
    }

    public function setSuccess(string $message = '', int $showFlag = self::SHOW_FLAG_ALL): void
    {
        $this->setStatus(self::STATUS_SUCCESS);
        if (!empty($message)) {
            $this->addMessage($this->manualStatus, $message, $showFlag);
        }
    }

    public function setFailure(string $message = '', int $showFlag = self::SHOW_FLAG_ALL): void
    {
        $this->setStatus(self::STATUS_FAILURE);
        if (!empty($message)) {
            $this->addMessage($this->manualStatus, $message, $showFlag);
        }
    }

    public function getStatus(): int
    {
        return $this->manualStatus;
    }

    public function isSuccessful(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === self::STATUS_FAILURE;
    }

    public function addMessage(int $status, string $message, int $showFlag = self::SHOW_FLAG_ALL)
    {
        $this->messageList[$status][] = ['message' => $message, 'show_flag' => $showFlag];
    }

    public function getMessages($status, int $showFlag = self::SHOW_FLAG_ALL): array
    {
        if (!array_key_exists($status, $this->messageList)) {
            return [];
        }

        $result = array_filter($this->messageList[$status], function ($messageItem) use ($showFlag) {
            return $messageItem['show_flag'] === $showFlag
                || $messageItem['show_flag'] === self::SHOW_FLAG_ALL;
        });

        return array_unique(array_column($result, 'message'));
    }

    public function getAllMessages(int $showFlag = self::SHOW_FLAG_ALL): array
    {
        return array_reduce(array_keys($this->messageList), function ($messageItem, $status) use ($showFlag) {
            return array_merge($messageItem, $this->getMessages($status, $showFlag));
        }, []);
    }

    public function getStatusMessage(int $showFlag = self::SHOW_FLAG_ALL): array
    {
        return $this->getMessages($this->manualStatus, $showFlag);
    }
}