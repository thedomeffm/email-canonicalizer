<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Matcher;

interface DomainMatcherInterface
{
    /**
     * @param string $domain the lowercased domain, in punycode for internationalized domains
     */
    public function matches(string $domain): bool;
}
