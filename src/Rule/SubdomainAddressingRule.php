<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\EmailAddress;

/**
 * Subdomain addressing: "tag@user.example.com" reaches "user@example.com". The subdomain becomes
 * the local part, and the old local part is dropped. Only one subdomain level below a base domain counts.
 */
final readonly class SubdomainAddressingRule implements RuleInterface
{
    /**
     * @var list<string>
     */
    private array $baseDomains;

    public function __construct(string ...$baseDomains)
    {
        if ($baseDomains === []) {
            throw new InvalidArgumentException('At least one base domain is required.');
        }

        $this->baseDomains = array_values($baseDomains);
    }

    public function apply(EmailAddress $emailAddress): EmailAddress
    {
        if (in_array($emailAddress->domain, $this->baseDomains, true)) {
            return $emailAddress;
        }

        $dotPosition = strpos($emailAddress->domain, '.');

        if ($dotPosition === false || $dotPosition === 0) {
            return $emailAddress;
        }

        $baseDomain = substr($emailAddress->domain, $dotPosition + 1);

        if (! in_array($baseDomain, $this->baseDomains, true)) {
            return $emailAddress;
        }

        return new EmailAddress(substr($emailAddress->domain, 0, $dotPosition), $baseDomain);
    }
}
