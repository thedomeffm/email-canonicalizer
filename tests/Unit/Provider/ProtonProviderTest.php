<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\ProtonProvider;

test('it is named proton', function (): void {
    expect((new ProtonProvider())->name())->toBe('proton');
});

test('it matches the four proton domains', function (string $domain, bool $expected): void {
    expect((new ProtonProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'proton.me' => ['proton.me', true],
    'protonmail.com' => ['protonmail.com', true],
    'protonmail.ch' => ['protonmail.ch', true],
    'pm.me' => ['pm.me', true],
    'other tld' => ['proton.com', false],
    'subdomain' => ['mail.proton.me', false],
]);

test('it removes the subaddress and the dots, underscores and hyphens', function (string $localPart, string $expected): void {
    expect(Canonical::email(new ProtonProvider(), $localPart . '@proton.me'))->toBe($expected . '@proton.me');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots' => ['john.doe', 'johndoe'],
    'underscores' => ['john_doe', 'johndoe'],
    'hyphens' => ['john-doe', 'johndoe'],
    'all three' => ['j.o_h-n.doe', 'johndoe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'punctuation and subaddress' => ['john.doe_x+news.letter-x', 'johndoex'],
    'uppercase' => ['John.Doe', 'johndoe'],
]);

test('it keeps each domain and does not merge them', function (string $domain): void {
    expect(Canonical::email(new ProtonProvider(), 'John.Doe+shopping@' . strtoupper($domain)))->toBe('johndoe@' . $domain);
})->with(['proton.me', 'protonmail.com', 'protonmail.ch', 'pm.me']);
