# DSN Parser

![](https://img.shields.io/badge/8.2|8.3-blue.svg)
![PHP Package](https://github.com/efureev/php-dsn/workflows/PHP%20Package/badge.svg?branch=master)
[![Build Status](https://travis-ci.org/efureev/php-dsn.svg?branch=master)](https://travis-ci.org/efureev/php-dsn)
[![Latest Stable Version](https://poser.pugx.org/efureev/php-dsn/v/stable?format=flat)](https://packagist.org/packages/efureev/php-dsn)
[![Total Downloads](https://poser.pugx.org/efureev/php-dsn/downloads)](https://packagist.org/packages/efureev/php-dsn)

## Description

There is no official DSN RFC. We have defined a DSN configuration string as using the following definition. 
The "URL looking" parts of a DSN is based from [RFC 3986](https://datatracker.ietf.org/doc/html/rfc3986).

## Kinds

### String DSN

Template: `<scheme>://<username>:<password>@<host>:<port>/<database>`

- `http://localhost`
- `localhost:8080`
- `https://examlple.com`
- `examlple.com`
- `http://127.0.0.1/foo/bar?key=value`
- `memcached://127.0.0.1`
- `memcached:///var/local/run/memcached.socket?weight=25`
- `mysql://john:pass@localhost:3306/my_db`
- `scheme:///var/local/run/memcached.socket?weight=25`


### Parameters DSN
Template: `<scheme>:host=<host>;port=<port>;dbname=<database>`

- `mysql:host=localhost;dbname=example`
- `ocdb://?Driver=ODBC+Driver+13+for+SQL+Server&server=localhost&database=WideWorldImporters&trusted_connection=Yes`

## Install

For php >= 8.2

```bash
composer require efureev/php-dsn "^1.0"
```

## Test

```bash
composer test
composer test-cover # with coverage
```
