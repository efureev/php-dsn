<?php

declare(strict_types=1);

namespace Php\Dns;

use Stringable;

use function array_key_exists;

class Dsn implements Stringable
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private ?string $scheme = null,
        private array $parameters = []
    ) {
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function __toString(): string
    {
        $scheme = $this->getScheme();

        return (empty($scheme) ? '' : $scheme . '://') . $this->parametersToString(false);
    }

    protected function parametersToString(bool $clear = true): string
    {
        $params = $this->getParameters();
        if (empty($params)) {
            return '';
        }

        $str = http_build_query($params);

        return $clear ? $str : '?' . $str;
    }
}
