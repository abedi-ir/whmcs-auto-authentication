## WHMCS Script for Automatic Login and Invoice Access

### Introduction

This WHMCS script allows users to automatically log in and be redirected to the invoice viewing page by entering the script's URL and providing a few parameters in the URL.

### Prerequisites

* Installed and configured WHMCS
* Basic PHP and Database knowledge

### Installation

1. Upload the script to an accessible directory on your WHMCS server.
2. Add `auto_auth_token` column to the WHMCS client table (Alternatively, you can import the database.sql file into your database).
3. Generate a unique token (64 characters long) for each client who wants to be able to log in from this script. You can generate random tokens in Linux with the command `openssl rand -hex 32`. (Provide these tokens to your clients so they can sign requests with them).
4. Links will expire and will not be accepted after 15 minutes by default. (You can change this by adding the `$autoAuthLinksMaxLife` property to your WHMCS `configuration.php` file and setting your desired maximum lifetime in seconds).
4. ENJOY.

### Usage

1. Open the script's URL in your browser.
2. Enter your email address in the `email` parameters.
3. Enter the desired invoice ID in the `invoice_id` parameter.
4. Enter the current time in seconds in the `timestamp` parameter. ([What is timestamp?](https://en.wikipedia.org/wiki/Unix_time))
5. Sign the request and put the hash value in `signature` parameter.

### Example

https://your-whmcs-domain.com/auto-authentication-view-invoice.php?email=user@example.com&invoice_id=123&timestamp=1658360000&signature=hash_value

In this example, the user with email `user@example.com` will be automatically logged in and redirected to the invoice page with ID 123.

## How to sign the request (signature parameter)

To generate the signature, simply concatenate the `email`, `timestamp` and the client's auto-auth token (created in step 3 of the installation process and provided to clients by the WHMCS admin), and then create a hash of the resulting string.

### Excample

```php
<?php

$email = 'user@example.com';
$timestamp = time();
$autoAuthToken = 'db1e22094965fa1c5b5a2c7eefe9e04b3c3c08b9d2f6af0fe0e0554af45d821b';
$invoiceId = 123;

$signature = password_hash($email.$timestamp.$autoAuthToken, PASSWORD_DEFAULT);

echo "https://your-whmcs-domain.com/auto-authentication-view-invoice.php?email={$email}&invoice_id={$invoiceId}&timestamp={$timestamp}&signature={$signature}";
