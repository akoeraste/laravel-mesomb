# Documentation

## Table of Contents

| Method | Description |
|--------|-------------|
| [**Application**](#Application) |  |
| [Application::status](#Applicationstatus) | Get Cached Application Status \| if null request fresh copy of Application Status. |
| [Application::checkStatus](#ApplicationcheckStatus) | Fetch Application Status. |
| [**Collect**](#Collect) |  |
| [Collect::__construct](#Collect__construct) |  |
| [Collect::pay](#Collectpay) | Send Collect Request. |
| [Collect::setCustomer](#CollectsetCustomer) | Details on the customer performing the payment. This will help MeSomb to build for you analytics based on customer (Example: Top N customers) |
| [Collect::setLocation](#CollectsetLocation) | Location for where the transaction was done. This will help MeSomb to build for you location based analytics based on customer (Example: transactions per region) |
| [Collect::setProduct](#CollectsetProduct) | Give details on the product purchase will help for product-based analytics |
| [**Deposit**](#Deposit) |  |
| [Deposit::__construct](#Deposit__construct) |  |
| [Deposit::pay](#Depositpay) | Make Deposit Request. |
| [**Signature**](#Signature) |  |
| [Signature::signRequest](#SignaturesignRequest) |  |
| [Signature::nonceGenerator](#SignaturenonceGenerator) | Generate a random string by the length |
| [**Transaction**](#Transaction) |  |
| [Transaction::generateURL](#TransactiongenerateURL) | Generate Checking URL. |
| [Transaction::checkStatus](#TransactioncheckStatus) | Check Transaction status. |

## Application





* Full name: \Hachther\MeSomb\Operation\Payment\Application


### Application::status

Get Cached Application Status | if null request fresh copy of Application Status.

```php
Application::status(  ): array|\Hachther\MeSomb\Operation\Payment\json
```



* This method is **static**.

**Return Value:**





---
### Application::checkStatus

Fetch Application Status.

```php
Application::checkStatus(  ): array
```



* This method is **static**.

**Return Value:**





---
## Collect





* Full name: \Hachther\MeSomb\Operation\Payment\Collect


### Collect::__construct



```php
Collect::__construct( string payer, int amount, string service, string country = 'CM', string currency = 'XAF', bool fees = true, bool conversion = true, string|null message = null, string|null redirect = null ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `payer` | **string** | the account number to collect from |
| `amount` | **int** | amount to collect |
| `service` | **string** | MTN, ORANGE, AIRTEL |
| `country` | **string** | country CM, NE |
| `currency` | **string** | code of the currency of the amount |
| `fees` | **bool** | if you want MeSomb to deduct he fees in the collected amount |
| `conversion` | **bool** | In case of foreign currently defined if you want to rely on MeSomb to convert the amount in the local currency |
| `message` | **string\|null** | Message to include in the transaction |
| `redirect` | **string\|null** | Where to redirect after the payment |


**Return Value:**





---
### Collect::pay

Send Collect Request.

```php
Collect::pay(  ): \Hachther\MeSomb\Model\Payment|null
```





**Return Value:**





---
### Collect::setCustomer

Details on the customer performing the payment. This will help MeSomb to build for you analytics based on customer (Example: Top N customers)

```php
Collect::setCustomer( array&lt;string,string&gt; customer ): void
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `customer` | **array&lt;string,string&gt;** | = {&#039;email&#039;: string, &#039;phone&#039;: string, &#039;town&#039;: string, &#039;region&#039;: string, &#039;country&#039;: string, &#039;first_name&#039;: string, &#039;last_name&#039;: string, &#039;address&#039;: string |


**Return Value:**





---
### Collect::setLocation

Location for where the transaction was done. This will help MeSomb to build for you location based analytics based on customer (Example: transactions per region)

```php
Collect::setLocation( array&lt;string,string&gt; location ): void
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `location` | **array&lt;string,string&gt;** | {&#039;town&#039;: string, &#039;region&#039;: string, &#039;country&#039;: string} |


**Return Value:**





---
### Collect::setProduct

Give details on the product purchase will help for product-based analytics

```php
Collect::setProduct( array product ): void
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | **array** | {&#039;id&#039;: string, &#039;name&#039;: string, &#039;category&#039;: string } |


**Return Value:**





---
## Deposit





* Full name: \Hachther\MeSomb\Operation\Payment\Deposit


### Deposit::__construct



```php
Deposit::__construct( string receiver, int amount, string service, string country = 'CM', string currency = 'XAF', bool conversion = true ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `receiver` | **string** | receiver account (in the local phone number) |
| `amount` | **int** | the amount of the transaction |
| `service` | **string** | service code (MTN, ORANGE, AIRTEL, ...) |
| `country` | **string** | country code &#039;CM&#039; by default |
| `currency` | **string** | currency of the transaction (XAF, XOF, ...) XAF by default |
| `conversion` | **bool** | In case of foreign currently defined if you want to rely on MeSomb to convert the amount in the local currency |


**Return Value:**





---
### Deposit::pay

Make Deposit Request.

```php
Deposit::pay(  ): \Hachther\MeSomb\Model\Deposit
```





**Return Value:**





---
## Signature





* Full name: \Hachther\MeSomb\Operation\Signature


### Signature::signRequest



```php
Signature::signRequest( string service, string method, string url, \DateTime date, string nonce, array credentials, array headers = [], array|null body = null ): string
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `service` | **string** | service to use can be payment, wallet ... (the list is provide by MeSomb) |
| `method` | **string** | HTTP method (GET, POST, PUT, PATCH, DELETE...) |
| `url` | **string** | the full url of the request with query element https://mesomb.hachther.com/path/to/ressource?highlight=params#url-parsing |
| `date` | **\DateTime** | Datetime of the request |
| `nonce` | **string** | Unique string generated for each request sent to MeSomb |
| `credentials` | **array** | dict containing key =&gt; value for the credential provided by MeSOmb. {&#039;access&#039; =&gt; access_key, &#039;secret&#039; =&gt; secret_key} |
| `headers` | **array** | Extra HTTP header to use in the signature |
| `body` | **array\|null** | The dict containing the body you send in your request body |


**Return Value:**

Authorization to put in the header



---
### Signature::nonceGenerator

Generate a random string by the length

```php
Signature::nonceGenerator( int length = 40 ): string
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `length` | **int** |  |


**Return Value:**





---
## Transaction





* Full name: \Hachther\MeSomb\Operation\Payment\Transaction


### Transaction::generateURL

Generate Checking URL.

```php
Transaction::generateURL( string endpoint, array ids ): string
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `endpoint` | **string** |  |
| `ids` | **array** |  |


**Return Value:**





---
### Transaction::checkStatus

Check Transaction status.

```php
Transaction::checkStatus( \Hachther\MeSomb\Model\Deposit|\Hachther\MeSomb\Model\Payment model ): array
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `model` | **\Hachther\MeSomb\Model\Deposit\|\Hachther\MeSomb\Model\Payment** |  |


**Return Value:**





---
