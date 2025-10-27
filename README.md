# Laravel Airtel Money

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bmatovu/laravel-airtel-money.svg?style=flat-square)](https://packagist.org/packages/bmatovu/laravel-airtel-money)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bmatovu/laravel-airtel-money/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bmatovu/laravel-airtel-money/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bmatovu/laravel-airtel-money/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bmatovu/laravel-airtel-money/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bmatovu/laravel-airtel-money.svg?style=flat-square)](https://packagist.org/packages/bmatovu/laravel-airtel-money)

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

**Authentication & KYC**

```php
use Bmatovu\AirtelMoney\Facades\AirtelMoney;

$token = AirtelMoney::getToken();

$user  = AirtelMoney::getUser($phoneNumber);
```

**Collections**

```php
use Bmatovu\AirtelMoney\Facades\Collection;

$transaction = Collection::receive($phoneNumber, $amount);

$transaction = Collection::refund($airtelMoneyId);

$transaction = Collection::getTransaction($transactionId);

$balance     = Collection::getBalance();
```

**Disbursement**

```php
use Bmatovu\AirtelMoney\Facades\Disbursement;

$transaction = Disbursement::send($phoneNumber, $amount);

$transaction = Disbursement::getTransaction($transactionId);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [mtvbrianking](https://github.com/mtvbrianking)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
