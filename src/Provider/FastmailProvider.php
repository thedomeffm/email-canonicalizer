<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Fastmail: removes "+tag", and turns subdomain addressing ("tag@user.fastmail.com")
 * into "user@fastmail.com". Dots in the local part count.
 * Supports the domains from Fastmail's official domain list, except the ones that look
 * like former Pobox domains. Each domain is kept as it is: the same name on two
 * Fastmail domains is not documented to be the same mailbox.
 *
 * Subdomain addressing moves part of the domain into the local part, so this provider
 * implements ProviderInterface directly instead of extending AbstractProvider.
 *
 * @see docs/providers.md#fastmail
 */
final readonly class FastmailProvider implements ProviderInterface
{
    /**
     * @var list<string>
     */
    private const array DOMAINS = [
        // Global domains
        '123mail.org',
        '150mail.com',
        '150ml.com',
        '16mail.com',
        '2-mail.com',
        '4email.net',
        '50mail.com',
        'airpost.net',
        'allmail.net',
        'cluemail.com',
        'elitemail.org',
        'emailcorner.net',
        'emailengine.net',
        'emailengine.org',
        'emailgroups.net',
        'emailplus.org',
        'emailuser.net',
        'eml.cc',
        'f-m.fm',
        'fast-email.com',
        'fast-mail.org',
        'fastem.com',
        'fastemailer.com',
        'fastest.cc',
        'fastimap.com',
        'fastmail.ca',
        'fastmail.cn',
        'fastmail.com',
        'fastmail.com.au',
        'fastmail.fm',
        'fastmail.im',
        'fastmail.in',
        'fastmail.jp',
        'fastmail.mx',
        'fastmail.net',
        'fastmail.org',
        'fastmail.to',
        'fastmail.tw',
        'fastmailbox.net',
        'fastmessaging.com',
        'fea.st',
        'fmailbox.com',
        'fmgirl.com',
        'fmguy.com',
        'ftml.net',
        'hailmail.net',
        'imap-mail.com',
        'imap.cc',
        'imapmail.org',
        'inoutbox.com',
        'internet-e-mail.com',
        'internet-mail.org',
        'internetemails.net',
        'internetmailing.net',
        'jetemail.net',
        'justemail.net',
        'letterboxes.org',
        'mail-central.com',
        'mail-page.com',
        'mailas.com',
        'mailbolt.com',
        'mailc.net',
        'mailcan.com',
        'mailforce.net',
        'mailhaven.com',
        'mailingaddress.org',
        'mailite.com',
        'mailmight.com',
        'mailnew.com',
        'mailsent.net',
        'mailservice.ms',
        'mailup.net',
        'mailworks.org',
        'ml1.net',
        'mm.st',
        'myfastmail.com',
        'mymacmail.com',
        'nospammail.net',
        'ownmail.net',
        'petml.com',
        'postinbox.com',
        'postpro.net',
        'proinbox.com',
        'promessage.com',
        'realemail.net',
        'reallyfast.biz',
        'reallyfast.info',
        'rushpost.com',
        'sent.as',
        'sent.at',
        'sent.com',
        'speedpost.net',
        'speedymail.org',
        'ssl-mail.com',
        'swift-mail.com',
        'the-fastest.net',
        'the-quickest.com',
        'theinternetemail.com',
        'veryfast.biz',
        'veryspeedy.net',
        'warpmail.net',
        'xsmail.com',
        'yepmail.net',
        'your-mail.com',
        // EU region domains
        'fastmail.co.uk',
        'fastmail.de',
        'fastmail.es',
        'fastmail.fr',
        'fastmail.nl',
        'fastmail.se',
        'fastmail.uk',
        'fmail.co.uk',
        // US region domains
        'bestmail.us',
        'fastemail.us',
        'fastmail.us',
        'h-mail.us',
    ];

    public function name(): string
    {
        return 'fastmail';
    }

    public function supports(string $domain): bool
    {
        return in_array($domain, self::DOMAINS, true) || $this->subdomainAddress($domain) instanceof EmailAddress;
    }

    public function applyRules(EmailAddress $emailAddress): EmailAddress
    {
        $subdomainAddress = $this->subdomainAddress($emailAddress->domain);

        if ($subdomainAddress instanceof EmailAddress) {
            return $subdomainAddress;
        }

        return new EmailAddress((new SubaddressRule('+'))->apply($emailAddress->localPart), $emailAddress->domain);
    }

    /**
     * Returns "user@fastmail.com" for the domain "user.fastmail.com". Fastmail supports only
     * one subdomain level, and the alias can't contain an underscore or a dot.
     */
    private function subdomainAddress(string $domain): ?EmailAddress
    {
        $dotPosition = strpos($domain, '.');

        if ($dotPosition === false || $dotPosition === 0) {
            return null;
        }

        $alias = substr($domain, 0, $dotPosition);
        $baseDomain = substr($domain, $dotPosition + 1);

        if (str_contains($alias, '_') || ! in_array($baseDomain, self::DOMAINS, true)) {
            return null;
        }

        return new EmailAddress($alias, $baseDomain);
    }
}
