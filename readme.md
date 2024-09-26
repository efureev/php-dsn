# DSN Parser

[![PHP Package](https://github.com/efureev/php-dsn/actions/workflows/php.yml/badge.svg)](https://github.com/efureev/php-dsn/actions/workflows/php.yml)
[![Latest Stable Version](http://poser.pugx.org/efureev/dsn/v)](https://packagist.org/packages/efureev/dsn)
[![Total Downloads](http://poser.pugx.org/efureev/dsn/downloads)](https://packagist.org/packages/efureev/dsn)
[![Latest Unstable Version](http://poser.pugx.org/efureev/dsn/v/unstable)](https://packagist.org/packages/efureev/dsn)
[![License](http://poser.pugx.org/efureev/dsn/license)](https://packagist.org/packages/efureev/dsn)
[![PHP Version Require](http://poser.pugx.org/efureev/dsn/require/php)](https://packagist.org/packages/efureev/dsn)
[![Dependents](http://poser.pugx.org/efureev/dsn/dependents)](https://packagist.org/packages/efureev/dsn)

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
composer require efureev/dsn "^1.0"
```

## Test

```bash
composer test
```
