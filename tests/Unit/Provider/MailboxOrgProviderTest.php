<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\MailboxOrgProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new MailboxOrgProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named mailbox-org', function (): void {
    expect((new MailboxOrgProvider())->name())->toBe('mailbox-org');
});

test('it supports only mailbox.org', function (string $domain, bool $expected): void {
    expect((new MailboxOrgProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'mailbox.org' => ['mailbox.org', true],
    'other tld' => ['mailbox.de', false],
    'subdomain' => ['mail.mailbox.org', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    expect(Canonical::email(new MailboxOrgProvider(), $localPart . '@mailbox.org'))->toBe($expected . '@mailbox.org');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it canonicalizes mailbox.org addresses end to end', function (): void {
    expect(Canonical::email(new MailboxOrgProvider(), ' John.Doe+Shop@MAILBOX.org '))->toBe('john.doe@mailbox.org');
});
