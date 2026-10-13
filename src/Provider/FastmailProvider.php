<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Fastmail: removes "+tag", and turns subdomain addressing ("tag@user.fastmail.com")
 * into "user@fastmail.com". Dots in the local part count.
 * Only fastmail.com is included, because Fastmail has no official list of its other domains.
 *
 * Subdomain addressing moves part of the domain into the local part, so this provider
 * implements ProviderInterface directly instead of extending AbstractProvider.
 *
 * @see docs/providers.md#fastmail
 */
final readonly class FastmailProvider implements ProviderInterface
{
    private const string DOMAIN = 'fastmail.com';

    public function name(): string
    {
        return 'fastmail';
    }

    public function supports(string $domain): bool
    {
        return $domain === self::DOMAIN || $this->subdomainAlias($domain) !== null;
    }

    public function applyRules(EmailAddress $emailAddress): EmailAddress
    {
        $subdomainAlias = $this->subdomainAlias($emailAddress->domain);

        if ($subdomainAlias !== null) {
            return new EmailAddress($subdomainAlias, self::DOMAIN);
        }

        return new EmailAddress((new SubaddressRule('+'))->apply($emailAddress->localPart), self::DOMAIN);
    }

    /**
     * Returns "user" for "user.fastmail.com". Fastmail supports only one subdomain level,
     * and the alias can't contain an underscore or a dot.
     */
    private function subdomainAlias(string $domain): ?string
    {
        $suffix = '.' . self::DOMAIN;

        if (! str_ends_with($domain, $suffix)) {
            return null;
        }

        $alias = substr($domain, 0, -strlen($suffix));

        if ($alias === '' || str_contains($alias, '.') || str_contains($alias, '_')) {
            return null;
        }

        return $alias;
    }
}
