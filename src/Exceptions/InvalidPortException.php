<?php

declare(strict_types=1);

namespace Php\Dns\Exceptions;

use InvalidArgumentException;

class InvalidPortException extends InvalidArgumentException
{
    public function __construct(
        public readonly string|int $port,
        string $message = 'Invalid Port'
    ) {
        parent::__construct(sprintf('%s (%s)', $message, (string)$port));
    }
}
