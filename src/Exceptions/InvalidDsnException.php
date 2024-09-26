<?php

declare(strict_types=1);

namespace Php\Dns\Exceptions;

use InvalidArgumentException;

class InvalidDsnException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $dsn,
        string $message = 'Invalid DSN string'
    ) {
        parent::__construct(sprintf('%s (%s)', $message, $dsn));
    }
}
