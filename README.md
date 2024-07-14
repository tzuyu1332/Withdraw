# 虛擬帳戶提領
一次提領所有可提領金額，根據不同金流商，執行不同的提領邏輯。

## 目錄
1. [工具](#工具)
2. [用法](#用法)

## 工具
1. **PHP**
    ```text
   7.4
    ```
   
2. **Laravel**
   ```text
   6
   ```

## 用法

```php
use App\Services\Withdraw\WithdrawService;
use Exception;

$memberId = 123; 

try {
   $withdrawService = new WithdrawService();
   $withdrawService->withdraw($memberId);
} catch (Exception $e) {
   echo $e->getMessage();
}
