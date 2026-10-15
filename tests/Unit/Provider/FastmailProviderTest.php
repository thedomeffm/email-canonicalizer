<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Matcher\FastmailDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\FastmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new FastmailProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named fastmail', function (): void {
    expect((new FastmailProvider())->name())->toBe('fastmail');
});

test('it uses the fastmail domain matcher', function (): void {
    expect((new FastmailProvider())->domainMatcher())->toBeInstanceOf(FastmailDomainMatcher::class);
});

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    expect(Canonical::email(new FastmailProvider(), $localPart . '@fastmail.com'))->toBe($expected . '@fastmail.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'john.doe'],
    'uppercase' => ['John+Shop', 'john'],
]);

test('it keeps each fastmail domain and does not merge them', function (string $email, string $expected): void {
    expect(Canonical::email(new FastmailProvider(), $email))->toBe($expected);
})->with([
    'plus addressing' => ['john+shopping@fastmail.fm', 'john@fastmail.fm'],
    'subdomain addressing' => ['shopping@john.fastmail.fm', 'john@fastmail.fm'],
    'three-label domain' => ['shopping@john.fastmail.co.uk', 'john@fastmail.co.uk'],
    'non-fastmail brand' => ['john+shopping@sent.com', 'john@sent.com'],
]);

test('it moves the subdomain into the local part', function (string $localPart): void {
    expect(Canonical::email(new FastmailProvider(), $localPart . '@john.fastmail.com'))->toBe('john@fastmail.com');
})->with([
    'newsletter',
    'news.letter',
    'news+letter',
]);

test('it canonicalizes fastmail addresses end to end', function (string $email): void {
    expect(Canonical::email(new FastmailProvider(), $email))->toBe('john@fastmail.com');
})->with([
    'john@fastmail.com',
    'John+Shopping@FastMail.com',
    ' Shopping@John.FastMail.com ',
]);

test('it leaves unsupported subdomains alone', function (string $email): void {
    expect(Canonical::email(new FastmailProvider(), $email))->toBe($email);
})->with([
    'two subdomain levels' => ['news@a.john.fastmail.com'],
    'pobox domain' => ['news@john.pobox.com'],
]);
