<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\FastmailDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;
use TheDomeFfm\EmailCanonicalizer\Rule\SubdomainAddressingRule;

/**
 * Fastmail: removes "+tag", and turns subdomain addressing ("tag@user.fastmail.com")
 * into "user@fastmail.com". Dots in the local part count.
 * Covers the domains from Fastmail's official domain list (see FastmailDomainMatcher), except the ones
 * that look like former Pobox domains. Each domain is kept as it is: the same name on two
 * Fastmail domains is not documented to be the same mailbox.
 *
 * @see docs/providers.md#fastmail
 */
final readonly class FastmailProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'fastmail';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new FastmailDomainMatcher();
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubdomainAddressingRule(...FastmailDomainMatcher::DOMAINS), new SubaddressRule('+')];
    }
}
