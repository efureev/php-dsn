<?php

declare(strict_types=1);

namespace Php\Dns;

use Php\Dns\Exceptions\InvalidPortException;
use Php\Dns\Exceptions\SyntaxException;

class StringParser
{
    private const UNRESERVED        = 'a-zA-Z0-9-\._~';
    private const SUB_DELIMS        = '!\$&\'\(\}\*\+,;=';
    private const VALID952_HOSTNAME = '(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])';

    public function __construct(private bool $urlDecode = true)
    {
    }

    public function parse(string $dsn): Dsn
    {
        if (!str_contains($dsn, ':')) {
            if (!str_contains($dsn, '.')) {
                // localhost
                // redis
                if (!preg_match('#^' . self::VALID952_HOSTNAME . '$#', $dsn)) {
                    var_dump('#^' . self::VALID952_HOSTNAME . '$#');
                    throw new SyntaxException($dsn, 'A DSN must contain a hostname [a-zA-Z0-9\+-\.]+ from RFC 952');
                }

                if (mb_strlen($dsn) > 254) {
                    throw new SyntaxException($dsn, 'A hostname should be less 255 characters');
                }

                return new UrlDsn(null, $dsn);
            }

            $url = self::explodeUrl($dsn);
            // @example example.com
            // @phpstan-ignore-next-line
            return new UrlDsn(null, $url['host']);
        }

        // Find the scheme if it exists and trim the double slash.
        if (!preg_match('#^(?:(?<alt>[' . self::UNRESERVED . self::SUB_DELIMS . '%]+:[0-9]+(?:[/?].*)?)|(?<scheme>[a-zA-Z0-9\+-\.]+):(?://)?(?<dsn>.*))$#', $dsn, $matches)) {
            throw new SyntaxException($dsn, 'A DSN must contain a scheme [a-zA-Z0-9\+-\.]+ and a colon.');
        }

        $scheme = null;
        if (!empty($matches['scheme'])) {
            $scheme = $matches['scheme'];
            $dsn    = $matches['dsn'];
        }

        if ($dsn === '') {
            return new Dsn($scheme);
        }

//        // Parse user info
        if (!preg_match('#^(?:([' . self::UNRESERVED . self::SUB_DELIMS . '%]+)?(?::([' . self::UNRESERVED . self::SUB_DELIMS . '%]*))?@)?([^\s@]+)$#', $dsn, $matches)) {
            throw new SyntaxException($dsn, 'The provided DSN is not valid. Maybe you need to url-encode the user/password?');
        }

        $authentication = [
            'user'     => empty($matches[1]) ? null : urldecode($matches[1]),
            'password' => empty($matches[2]) ? null : urldecode($matches[2]),
        ];

        $url = self::explodeUrl($dsn);

        if ($this->urlDecode) {
            // @phpstan-ignore-next-line
            $url = array_map('rawurldecode', $url);
            // @phpstan-ignore-next-line
            $authentication = array_map('urldecode', $authentication);
        }

        if ($scheme === null) {
            // localhost:8080
            // example.com:80
            // @phpstan-ignore-next-line
            return new UrlDsn(null, $url['host'], self::toPort($url['port'] ?? null), $url['path'] ?? null, self::parseQuery($url));
        }

        if ('/' === $matches[3][0]) {
            $parts = self::explodeUrl($matches[3], $dsn);
            // @phpstan-ignore-next-line
            return new PathDsn($scheme, $parts['path'], self::parseQuery($parts), $authentication);
        }

        if ('?' === $matches[3][0]) {
            $parts = self::explodeUrl('f://t' . $matches[3], $dsn);

            return new Dsn($scheme, self::parseQuery($parts));
        }

        $parts = self::explodeUrl('f://' . $matches[3], $dsn);

        // @phpstan-ignore-next-line
        return new UrlDsn($scheme, $parts['host'], self::toPort($parts['port'] ?? null), $parts['path'] ?? null, self::parseQuery($url), $authentication);
    }

    private static function toPort(string|int|null $port): ?int
    {
        if ($port === null) {
            return null;
        }

        if (
            filter_var(
                $port,
                FILTER_VALIDATE_INT,
                [
                    "options" => [
                        "min_range" => 1,
                        "max_range" => 65535,
                    ],
                ]
            ) === false
        ) {
            throw new InvalidPortException($port);
        }

        return (int)$port;
    }

    /**
     * @return array{scheme?:string,host?:string,port?:int,query?:string,path?:string,user?:string,pass?:string}
     */
    private static function explodeUrl(string $url, string $dsn = null): array
    {
        $data = parse_url($url);
        if ($data === false) {
            throw new SyntaxException($dsn ?? $url, 'The provided DSN is not valid.');
        }

        return $data;
    }

    /**
     * @param array{query?:string} $parts
     * @return array<string, mixed>
     */
    private static function parseQuery(array $parts): array
    {
        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        // @phpstan-ignore-next-line
        return $query;
    }
}
