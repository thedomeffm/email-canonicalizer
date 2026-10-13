<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Rule\CharacterRule;
use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Consumer Gmail: removes "+tag" and ignores dots in the local part.
 * googlemail.com is rewritten to gmail.com. That alias is verified by a delivery test,
 * not by official Google documentation.
 *
 * @see docs/providers.md#gmail
 */
final class GmailProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'gmail';
    }

    protected function primaryDomain(): string
    {
        return 'gmail.com';
    }

    protected function aliasDomains(): array
    {
        return ['googlemail.com'];
    }

    /**
     * @return list<LocalPartRuleInterface>
     */
    protected function rules(): array
    {
        return [new SubaddressRule('+'), new CharacterRule(remove: ['.'])];
    }
}
