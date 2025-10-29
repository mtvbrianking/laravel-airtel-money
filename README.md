# Laravel Airtel Money

[![Latest Stable Version](https://poser.pugx.org/bmatovu/laravel-airtel-money/v/stable)](https://packagist.org/packages/bmatovu/laravel-airtel-money)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Tests](https://github.com/mtvbrianking/laravel-airtel-money/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-airtel-money/actions?query=workflow:run-tests)

### Prerequisites

You will need the following to get started with you integration...

1. Create an `application` on the [AirtelMoney Developer Portal](https://developers.airtel.africa/user/signup).

2. Write to the [AirtelMoney Support Team](https://developers.airtel.africa/user/support) to get your application approved, [here](mailto:kyc@ug.airtel.com?subject=AirtelMoney%20KYC%20submission).

Note: Your application won't work until it's approved. You can check the statuses in the portal. 

| Status             | Explanation   |
|--------------------|---------------|
| NA                 | Not Approved  |
| Partially Approved | UAT Approved  |
| Approved           | PROD Approved |

## Getting started

**Installation**

```bash
composer require bmatovu/laravel-airtel-money
```

**Publishables**

```bash
php artisan vendor:publish --provider="Bmatovu\AirtelMoney\AirtelMoneyServiceProvider"
```

**Migrations**

```bash
php artisan migrate
```

## Authentication & PIN

**Set credentials**

Get the `client_id` and `client_secret` from **Key Management** 

```bash
php artisan airtel-money:auth
```

**Set disbursement PIN**

```bash
php artisan airtel-money:pin
```

## Usage

**Authentication**

```php
use Bmatovu\AirtelMoney\Facades\Authentication;

$token = Authentication::getToken();
```

**Collections**

```php
use Bmatovu\AirtelMoney\Facades\Collection;

$transaction = Collection::receive($phoneNumber, $amount);

$transaction = Collection::refund($airtelMoneyId);

$transaction = Collection::getTransaction($transactionId);

$balance     = Collection::getBalance();

$user        = Collection::getUser($phoneNumber);
```

**Disbursement**

```php
use Bmatovu\AirtelMoney\Facades\Disbursement;

$transaction = Disbursement::send($phoneNumber, $amount);

$transaction = Disbursement::getTransaction($transactionId);

$user        = Disbursement::getUser($phoneNumber);
```

## Testing

```bash
composer test
```

## Credits

- [mtvbrianking](https://github.com/mtvbrianking)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
