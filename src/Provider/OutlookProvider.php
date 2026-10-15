<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Outlook.com: removes "+tag". Dots in the local part count.
 * Hotmail, Live, MSN and Outlook country domains are not included, because Microsoft
 * only documents outlook.com.
 *
 * @see docs/providers.md#outlookcom
 */
final readonly class OutlookProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'outlook';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher('outlook.com');
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
