<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Proton Mail: removes "+tag" on all four Proton domains. Dots in the local part count.
 * The domains are not merged: name@proton.me and name@protonmail.com are not
 * documented to be the same account.
 *
 * @see docs/providers.md#proton-mail
 */
final class ProtonProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'proton';
    }

    protected function primaryDomain(): string
    {
        return 'proton.me';
    }

    protected function additionalDomains(): array
    {
        return ['protonmail.com', 'protonmail.ch', 'pm.me'];
    }

    /**
     * @return list<LocalPartRuleInterface>
     */
    protected function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
