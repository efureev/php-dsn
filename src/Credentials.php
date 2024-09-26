<?php

declare(strict_types=1);

namespace Php\Dns;

use Stringable;

final readonly class Credentials implements Stringable
{
    public function __construct(
        public ?string $user,
        public ?string $password
    ) {
    }

    public function withUser(?string $user): self
    {
        return new self($user, $this->password);
    }

    public function withPassword(?string $password): self
    {
        return new self($this->user, $password);
    }

    public function isEmpty(): bool
    {
        return empty($this->user) && empty($this->password);
    }

    public function __toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        return $this->user . ('' === $this->password ? '' : ':' . $this->password);
    }
}
