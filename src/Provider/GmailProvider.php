<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RewriteDomainRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Consumer Gmail: removes "+tag" and ignores dots in the local part.
 * googlemail.com is rewritten to gmail.com, which Google documents as the same mailbox.
 *
 * @see docs/providers.md#gmail
 */
final readonly class GmailProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'gmail';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher('gmail.com', 'googlemail.com');
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+'), new RemoveCharactersRule('.'), new RewriteDomainRule('gmail.com', ['googlemail.com'])];
    }
}
