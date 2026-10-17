<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Proton Mail: removes "+tag" on all four Proton domains, and ignores dots, underscores and hyphens
 * in the local part (Proton treats them as transparent).
 * The domains are not merged: name@proton.me and name@protonmail.com are not
 * documented to be the same account.
 *
 * @see docs/providers.md#proton-mail
 */
final readonly class ProtonProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'proton';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher('proton.me', 'protonmail.com', 'protonmail.ch', 'pm.me');
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+'), new RemoveCharactersRule('.', '_', '-')];
    }
}
