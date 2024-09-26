<?php

declare(strict_types=1);

namespace Php\Dns;

trait WithCredentials
{
    private ?Credentials $auth = null;

    public function getAuthentication(): ?Credentials
    {
        return $this->auth;
    }

    /**
     * @param Credentials|array{user:string|null,password:string|null}|null $authentication
     * @return void
     */
    private function setAuthentication(Credentials|array|null $authentication): void
    {
        if (!empty($authentication)) {
            if (is_array($authentication)) {
                $authentication = new Credentials(
                    $authentication['user'] ?? null,
                    $authentication['password'] ?? null
                );
            }

            if (!$authentication->isEmpty()) {
                $this->auth = $authentication;
            }
        }
    }

    private function getUserInfoString(): string
    {
        if (!(string)$this->auth) {
            return '';
        }

        return $this->auth . '@';
    }
}
