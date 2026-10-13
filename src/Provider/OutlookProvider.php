<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Outlook.com: removes "+tag". Dots in the local part count.
 * Hotmail, Live, MSN and Outlook country domains are not included, because Microsoft
 * only documents outlook.com.
 *
 * @see docs/providers.md#outlookcom
 */
final class OutlookProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'outlook';
    }

    protected function primaryDomain(): string
    {
        return 'outlook.com';
    }

    /**
     * @return list<LocalPartRuleInterface>
     */
    protected function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
