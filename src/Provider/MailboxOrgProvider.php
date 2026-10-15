<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * mailbox.org: removes "+extension". Dots in the local part count.
 * Only mailbox.org itself is supported. Custom domains hosted there are out of scope.
 *
 * @see docs/providers.md#mailboxorg
 */
final readonly class MailboxOrgProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'mailbox-org';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher('mailbox.org');
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
