<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\AnyDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;

/**
 * Catch-all fallback: supports every domain and only lowercases the local part.
 * It has to be the last provider, because it matches everything.
 *
 * @see docs/providers.md#lowercase
 */
final readonly class LowercaseProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'lowercase';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new AnyDomainMatcher();
    }

    public function rules(): array
    {
        return [];
    }
}
