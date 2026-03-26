<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

final class EmailHelper
{
    /**
     * Extracts the base username (before the '+' and '@').
     * 'user+tag@example.com' -> 'user'.
     *
     * @throws \InvalidArgumentException if the email does not contain '@'
     */
    public function getBaseUsername(string $email): string
    {
        $this->assertValidEmailFormat($email);

        $username = explode('@', $email, 2)[0];

        return explode('+', $username, 2)[0];
    }

    /**
     * Extracts the domain from an email address.
     * 'user+tag@example.com' -> 'example.com'.
     *
     * @throws \InvalidArgumentException if the email does not contain '@'
     */
    public function getDomain(string $email): string
    {
        $this->assertValidEmailFormat($email);

        return explode('@', $email, 2)[1];
    }

    private function assertValidEmailFormat(string $email): void
    {
        if (!str_contains($email, '@')) {
            throw new \InvalidArgumentException(\sprintf('Invalid email format: "%s" does not contain "@".', $email));
        }
    }
}
