# iguanaIR for PHP

[![Build Status](https://travis-ci.org/mjanser/php-iguanair.svg?branch=master)](https://travis-ci.org/mjanser/php-iguanair)
[![Code Coverage](https://scrutinizer-ci.com/g/mjanser/php-iguanair/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mjanser/php-iguanair/?branch=master)

This library can be used to send signals to an [iguanaIR](https://www.iguanaworks.net/) device.
Internally it uses the [Symfony Process Component](https://symfony.com/doc/current/components/process.html) for running the `igclient` command.

## Requirements

- PHP 5.6 or higher
- `igclient` installed and functional

## Installation

Run the following composer command in your project:

```bash
composer require mjanser/iguanair
```

## Usage

Example usage:

```php
$client = new IguanaIr\Client();
//$client = new IguanaIr\Client('my-device');

$client->send('signals.txt');
$client->send('signals.txt', [1, 3]);
```
