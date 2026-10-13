<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;

/**
 * Base class for providers that are described by a primary domain, alias domains,
 * additional domains and local-part rules.
 *
 * Every domain, alias and rule must be backed by the provider's official documentation
 * and listed with its source in docs/providers.md. Link the provider's section there
 * in the docblock of each subclass.
 *
 * @see docs/providers.md
 */
abstract class AbstractProvider implements ProviderInterface
{
    public function supports(string $domain): bool
    {
        return $domain === $this->primaryDomain()
            || in_array($domain, $this->aliasDomains(), true)
            || in_array($domain, $this->additionalDomains(), true);
    }

    public function applyRules(EmailAddress $emailAddress): EmailAddress
    {
        $localPart = $emailAddress->localPart;

        foreach ($this->rules() as $localPartRule) {
            $result = $localPartRule->apply($localPart);

            // An empty local part would produce a broken address, so the rule is skipped.
            if ($result !== '') {
                $localPart = $result;
            }
        }

        $domain = in_array($emailAddress->domain, $this->aliasDomains(), true)
            ? $this->primaryDomain()
            : $emailAddress->domain;

        return new EmailAddress($localPart, $domain);
    }

    /**
     * The provider's most common domain, lowercase. All alias domains are rewritten to it.
     */
    abstract protected function primaryDomain(): string;

    /**
     * Lowercase domains that reach the same mailbox as the primary domain.
     * They are rewritten to the primary domain.
     *
     * @return list<string>
     */
    protected function aliasDomains(): array
    {
        return [];
    }

    /**
     * Lowercase domains the rules apply to, but that are separate mailboxes.
     * They are kept as they are.
     *
     * @return list<string>
     */
    protected function additionalDomains(): array
    {
        return [];
    }

    /**
     * Local-part rules, applied in order.
     *
     * @return list<LocalPartRuleInterface>
     */
    abstract protected function rules(): array;
}
