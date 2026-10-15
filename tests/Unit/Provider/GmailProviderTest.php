<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new GmailProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named gmail', function (): void {
    expect((new GmailProvider())->name())->toBe('gmail');
});

test('it matches gmail.com and googlemail.com', function (string $domain, bool $expected): void {
    expect((new GmailProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'gmail.com' => ['gmail.com', true],
    'googlemail.com' => ['googlemail.com', true],
    'subdomain' => ['mail.gmail.com', false],
    'other tld' => ['gmail.de', false],
]);

test('it removes the subaddress and dots', function (string $localPart, string $expected): void {
    expect(Canonical::email(new GmailProvider(), $localPart . '@gmail.com'))->toBe($expected . '@gmail.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots' => ['j.o.h.n.doe', 'johndoe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'johndoe'],
    'dots inside the subaddress' => ['john.doe+news.letter', 'johndoe'],
    'uppercase' => ['John.Doe', 'johndoe'],
]);

test('it rewrites googlemail.com to gmail.com', function (): void {
    expect(Canonical::email(new GmailProvider(), 'john.doe+newsletter@googlemail.com'))->toBe('johndoe@gmail.com');
});

test('it canonicalizes gmail addresses end to end', function (string $email): void {
    expect(Canonical::email(new GmailProvider(), $email))->toBe('johndoe@gmail.com');
})->with([
    'johndoe@gmail.com',
    'John.Doe@Gmail.com',
    ' j.o.h.n.d.o.e+shopping@GMAIL.COM ',
    'john.doe@googlemail.com',
    'John.Doe+News@GoogleMail.com',
]);
