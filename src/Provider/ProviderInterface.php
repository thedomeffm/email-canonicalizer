<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;

/**
 * Built-in providers must be documented with official sources in docs/providers.md.
 *
 * @see docs/providers.md
 */
interface ProviderInterface
{
    public function name(): string;

    /**
     * @param string $domain Expects the trimmed and lowercased domain
     */
    public function supports(string $domain): bool;

    /**
     * Receives the trimmed and lowercased address.
     */
    public function applyRules(EmailAddress $emailAddress): EmailAddress;
}
