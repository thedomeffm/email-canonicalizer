<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Posteo: removes "+filter" (Posteo calls them filter addresses). Dots in the local part count.
 * Supports the 13 domains Posteo offers for registering an address. The 49 domains that
 * are only offered for aliases are left out. Domains are kept as they are: Posteo doesn't
 * document that the same name on two domains is the same mailbox.
 *
 * @see docs/providers.md#posteo
 */
final readonly class PosteoProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'posteo';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher(
            'posteo.de',
            'posteo.com',
            'posteo.net',
            'posteo.us',
            'posteo.ca',
            'posteo.uk',
            'posteo.ie',
            'posteo.es',
            'posteo.at',
            'posteo.ch',
            'posteo.nl',
            'posteo.be',
            'posteo.lu',
        );
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
