<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

/**
 * Test double: removes dots from the local part for the configured domains.
 */
final readonly class StripDotsProvider implements ProviderInterface
{
    /**
     * @param list<string> $domains
     */
    public function __construct(
        private string $name = 'strip-dots',
        private array $domains = ['example.com'],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function supports(string $domain): bool
    {
        return in_array($domain, $this->domains, true);
    }

    public function applyRules(EmailAddress $emailAddress): EmailAddress
    {
        return new EmailAddress(str_replace('.', '', $emailAddress->localPart), $emailAddress->domain);
    }
}
