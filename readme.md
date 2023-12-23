# Laravel MeSomb

Laravel Wrapper on top of MeSomb Payment API

## Roadmap

API Features and their implementations [https://mesomb.hachther.com/en/api/v1.1/schema/](https://mesomb.hachther.com/en/api/v1.1/schema/)

| Feature              | Status  | Documentation                                                    |
|----------------------|---------|------------------------------------------------------------------|
| Payment              | &#9745; | [Check the documentation](docs/README.md#Collect)                |
| Transaction Status   | &#9745; | [Check the documentation](docs/README.md#TransactioncheckStatus) |
| Application Status   | &#9745; | [Check the documentation](docs/README.md#ApplicationcheckStatus) |
| Deposits             | &#9745; | [Check the documentation](docs/README.md#Deposit)                |
| Test                 | &#9744; |                                                                  |
| Better Documentation | &#9744; |                                                                  |

## Installation

Before you start, you must register your service and MeSomb and get API Access keys. Please follow [this tutorial](https://mesomb.hachther.com/en/blog/tutorials/how-to-register-your-service-on-mesomb/).

### Install Package

```shell
composer require hachther/laravel-mesomb
```

### Publish Configuration Files

Setting the following parameters from MeSomb

Get the information below from MeSomb after following the above tutorial.
```dotenv
MESOMB_APP_KEY=<ApplicationKey>
MESOMB_API_HOST=https://mesomb.hachther.com
MESOMB_API_VERSION=v1.1
MESOMB_ACCESS_KEY=<AccessKey>
MESOMB_SECRET_KEY=<SecretKey>
MESOMB_SSL_VERIFY=true
```

Publish configurations file

```shell
php artisan vendor:publish --tag=mesomb-configuration
```

### Migrate Mesomb Transaction Tables

```shell
php artisan migrate
```

## Usage

### Quick Examples

#### Simple Collect

```php
// OrderController.php
use Hachther\MeSomb\Operation\Payment\Collect;

class OrderController extends Controller {

    public function confirmOrder()
    {
        $request = new Collect('67xxxxxxx', 1000, 'MTN', 'CM');

        $payment = $request->pay();

        if($payment->success){
            // Fire some event,Pay someone, Alert user
        } else {
            // fire some event, redirect to error page
        }

        // get Transactions details $payment->transactions
    }
}
```

#### Simple Deposit

```php
// OrderController.php
use Hachther\MeSomb\Operation\Payment\Deposit;

class OrderController extends Controller {

    public function makeDeposit()
    {
        $request = new Deposit('67xxxxxxx', 1000, 'MTN', 'CM');

        $payment = $request->pay();

        if($payment->success){
            // Fire some event,Pay someone, Alert user
        } else {
            // fire some event, redirect to error page
        }

        // get Transactions details $payment->transactions
    }
}
```

#### Attaching Payments to Models Directly

```php

// Order.php

use Hachther\MeSomb\Helper\HasPayments;

class Order extends Model
{
    use HasPayments;
}

// OrderController.php

class OrderController extends Controller {

    public function confirmOrder(){

        $order = Order::create(['amount' => 100]);

        $payment  = $order->payment('67xxxxxxx', $order->amount, 'MTN', 'CM')->pay();

        if($payment->success){
            // Fire some event,Pay someone, Alert user
        } else {
            // fire some event, redirect to error page
        }

        // View Order payments via $order->payments

        // Get payment transaction with $payment->transaction

        return $payment;
    }
}
```

### Handle multiple applications

This is how you process if you want to handle multiple MeSomb applications with the same project.

1. Set up your configuration file with the default application and other information as specified below.
2. Update the applicationKey (the accessKey and the secretKey if needed) on the fly as you can see below.

```php
// OrderController.php
use Hachther\MeSomb\Operation\Payment\Collect;

class OrderController extends Controller {

    public function confirmOrder()
    {
        $request = new Collect('67xxxxxxx', 1000, 'MTN', 'CM');

        // Update applicationKey before process the payment
        // You also have setAccessKey and setSecretKey
        $payment = $request->setApplicationKey('<applicationKey>')->pay();

        if($payment->success){
            // Fire some event,Pay someone, Alert user
        } else {
            // fire some event, redirect to error page
        }

        // get Transactions details $payment->transactions
    }
}
```

## Author

Hachther LLC
[contact@hachther.com](contact@hachther.com)

Thank you to Malico ([hi@malico.me](hi@malico.me)) for starting this module.
