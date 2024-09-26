<?php

declare(strict_types=1);

namespace Php\Dns;

class PathDsn extends Dsn
{
    use WithCredentials;

    /**
     * @param array<string, mixed> $parameters
     * @param Credentials|array{user:string|null,password:string|null}|null $authentication
     */
    public function __construct(
        ?string $scheme,
        private string $path,
        array $parameters = [],
        Credentials|array|null $authentication = null
    ) {
        parent::__construct($scheme, $parameters);
        $this->setAuthentication($authentication);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return
            $this->getScheme() . '://' . $this->getUserInfoString() . $this->getPath() . $this->parametersToString(false);
    }
}
