<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;

/**
 * A provider is configuration: which domains it covers and which rules apply to them.
 * The canonicalizer does the work: it picks the first provider whose matcher matches the domain,
 * lowercases the local part, and applies the rules in order.
 *
 * Built-in providers must be documented with official sources in docs/providers.md.
 *
 * @see docs/providers.md
 */
interface ProviderInterface
{
    public function name(): string;

    public function domainMatcher(): DomainMatcherInterface;

    /**
     * Applied in order to the address with a lowercased local part and domain.
     * A rule result with an empty local part is ignored.
     *
     * @return list<RuleInterface>
     */
    public function rules(): array;
}
