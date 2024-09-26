<?php

declare(strict_types=1);

namespace Php\Dns;

class UrlDsn extends Dsn
{
    use WithCredentials;

    /**
     * @param array<string, mixed> $parameters
     * @param Credentials|array{user:string|null,password:string|null}|null $authentication
     */
    public function __construct(
        ?string $scheme,
        private string $host,
        private ?int $port = null,
        private ?string $path = null,
        array $parameters = [],
        Credentials|array|null $authentication = null
    ) {
        parent::__construct($scheme, $parameters);
        $this->setAuthentication($authentication);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        $scheme = $this->getScheme();

        return (empty($scheme) ? '' : $scheme . '://') . $this->getUserInfoString() . $this->getHost() . (empty($this->port) ? '' : ':' . $this->port) . ($this->getPath() ?? '') . $this->parametersToString(false);
    }
}
