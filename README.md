## Laravel Airtel Money

[![Latest Stable Version](https://poser.pugx.org/bmatovu/laravel-airtel-money/v/stable)](https://packagist.org/packages/bmatovu/laravel-airtel-money)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Tests](https://github.com/mtvbrianking/laravel-airtel-money/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-airtel-money/actions?query=workflow:run-tests)
[![Documentation](https://github.com/mtvbrianking/laravel-airtel-money/workflows/gen-docs/badge.svg)](https://mtvbrianking.github.io/laravel-airtel-money/main)

### Getting Started

To get started with your Airtel Money integration:

1. [**Register your application**](https://developers.airtel.africa/developer) on the Airtel Money Developer Portal.

2. **Add products to your application**: APIs are grouped into products.  
   - Start with: _Account, KYC, Collection-APIs, and Disbursement-APIs_.
   - Each product may have specific KYC/compliance requirements, which the Airtel Money team will advise during onboarding.

3. [**Request application approval**](https://developers.airtel.africa/user/support) from the Airtel Money Support Team.  
   - Your application will only work after it has been approved.
   - You can monitor the approval status in the developer portal.

4. **Whitelist your source IPs** in the developer portal under `Settings -> Security -> Server IP Allowed List`.  
   - Any collection or disbursement request originating from a non-whitelisted source IP **will be declined** at Airtel Money.

5. **Set disbursement PIN**: optional for collections.

6. **Set collection callback**: you can set an endpoint to where your callbacks should be set.
   Optionally, you can enable Authorization for it.

**Statuses**

| Mode (Env) | Required Status    |
| :--------- |:-------------------|
| TEST       | Partially Approved |
| PRODUCTION | Approved           |

_New Applications will have the default `NA` status._

**Products**

| Product | Functionality | Comment |
|:--------|:--------------|:--------|
| [KYC](https://developers.airtel.africa/documentation/kyc/1.0) | User Inquiry | Get user basic information like first and last name |
| [Account](https://developers.airtel.africa/documentation/account/1.0) | Balance Inquiry | Collection wallet balance check |
| [Collection APIs](https://developers.airtel.africa/documentation/collection-apis/2.0) | Payments (USSD Push) | Request a payment. User enters PIN to approve the transaction. |
| | Refund | Refund a previous collection transaction (partial or full) |
| | Callback (No Auth) | Sent to your callback URL upon transaction completion |
| | Callback (With Auth) | Sent to your callback URL with Authorization (must be enabled first) |
| | Transaction Inquiry | Check collection transaction details |
| [Disbursement APIs](https://developers.airtel.africa/documentation/collection-apis/2.0) | Payments | Send money to an Airtel number. Requires your PIN and prior PIN setup |
| | Transaction Inquiry | Check disbursement transaction details |

_The disbursement wallet has no dedicated balance-inquiry API; its running balance is returned with each transaction response._

### Integration

**Installation**

```bash
composer require bmatovu/laravel-airtel-money
````

**Publishables**

```bash
php artisan vendor:publish --provider="Bmatovu\AirtelMoney\AirtelMoneyServiceProvider"
```

**Database Migrations**

```bash
php artisan migrate
```

### Authorization & PIN

**Set Credentials**

Retrieve your `client_id` and `client_secret` from **Key Management**

```bash
php artisan airtel-money:auth
```

**Set Disbursement PIN**

```bash
php artisan airtel-money:pin
```

### Usage

[**Authorization**](https://developers.airtel.africa/documentation/authorization/1.0)

```php
use Bmatovu\AirtelMoney\Facades\Authorization;

$token = Authorization::getToken();
```

[**KYC**](https://developers.airtel.africa/documentation/kyc/1.0)

```php
use Bmatovu\AirtelMoney\Facades\Kyc;

$user = Kyc::getUser($phoneNumber);
```

[**Collection**](https://developers.airtel.africa/documentation/collection-apis/2.0)

```php
use Bmatovu\AirtelMoney\Facades\Collection;

$transaction = Collection::receive($phoneNumber, $amount);

$transaction = Collection::refund($airtelMoneyId);

$transaction = Collection::getTransaction($transactionId);
```

[**Account**](https://developers.airtel.africa/documentation/account/1.0)

```php
use Bmatovu\AirtelMoney\Facades\Account;

$balance = Account::getBalance();
```

[**Disbursement**](https://developers.airtel.africa/documentation/collection-apis/2.0)

```php
use Bmatovu\AirtelMoney\Facades\Disbursement;

$transaction = Disbursement::send($phoneNumber, $amount);

$transaction = Disbursement::getTransaction($transactionId);
```

### Testing

```bash
composer test
```

### Credits

* [mtvbrianking](https://github.com/mtvbrianking)
* [All Contributors](../../contributors)

### License

This package is open-source software licensed under the [MIT License](LICENSE.md).
