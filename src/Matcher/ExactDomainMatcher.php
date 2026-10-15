<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Matcher;

use InvalidArgumentException;

/**
 * Matches exactly the given domains. Subdomains don't match.
 */
final readonly class ExactDomainMatcher implements DomainMatcherInterface
{
    /**
     * @var list<string>
     */
    private array $domains;

    public function __construct(string ...$domains)
    {
        if ($domains === []) {
            throw new InvalidArgumentException('At least one domain is required.');
        }

        if (in_array('', $domains, true)) {
            throw new InvalidArgumentException('Domains must not be empty.');
        }

        $this->domains = array_values($domains);
    }

    public function matches(string $domain): bool
    {
        return in_array($domain, $this->domains, true);
    }
}
