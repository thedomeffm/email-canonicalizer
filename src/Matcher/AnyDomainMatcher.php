<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Matcher;

/**
 * Matches every domain. A provider using it has to be the last one, because it hides all after it.
 */
final readonly class AnyDomainMatcher implements DomainMatcherInterface
{
    public function matches(string $domain): bool
    {
        return true;
    }
}
