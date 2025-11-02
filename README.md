## Laravel Airtel Money

[![Latest Stable Version](https://poser.pugx.org/bmatovu/laravel-airtel-money/v/stable)](https://packagist.org/packages/bmatovu/laravel-airtel-money)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-airtel-money/?branch=main)
[![Tests](https://github.com/mtvbrianking/laravel-airtel-money/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-airtel-money/actions?query=workflow:run-tests)
[![Documentation](https://github.com/mtvbrianking/laravel-airtel-money/workflows/gen-docs/badge.svg)](https://mtvbrianking.github.io/laravel-airtel-money/main)

### Prerequisites

To get started with your Airtel Money integration:

1. Create an **Application** on the Airtel Money Developer Portal:  
   https://developers.airtel.africa/user/signup

2. Request application approval from the **Airtel Money Support Team**:  
   https://developers.airtel.africa/user/support  

3. Register your **source IP addresses** in the developer portal to avoid failed transaction requests.

**Note:** Your application will only work after approval. You can monitor its status in the portal.

| Status             | Meaning           |
|:-------------------|:------------------|
| NA                 | Not Approved      |
| Partially Approved | Approved for UAT  |
| Approved           | Approved for PROD |

### Getting Started

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

### Authentication & PIN

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

### Testing

```bash
composer test
```

### Credits

* [mtvbrianking](https://github.com/mtvbrianking)
* [All Contributors](../../contributors)

### License

This package is open-source software licensed under the [MIT License](LICENSE.md).
