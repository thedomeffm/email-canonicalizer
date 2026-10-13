<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Provider\ProtonProvider;

test('it is named proton', function (): void {
    expect((new ProtonProvider())->name())->toBe('proton');
});

test('it supports the four proton domains', function (string $domain, bool $expected): void {
    expect((new ProtonProvider())->supports($domain))->toBe($expected);
})->with([
    'proton.me' => ['proton.me', true],
    'protonmail.com' => ['protonmail.com', true],
    'protonmail.ch' => ['protonmail.ch', true],
    'pm.me' => ['pm.me', true],
    'other tld' => ['proton.com', false],
    'subdomain' => ['mail.proton.me', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    $emailAddress = (new ProtonProvider())->applyRules(new EmailAddress($localPart, 'proton.me'));

    expect($emailAddress->localPart)->toBe($expected);
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it keeps each domain and does not merge them', function (string $domain): void {
    $canonicalEmail = (new EmailCanonicalizer(new ProtonProvider()))->canonicalize('John.Doe+shopping@' . strtoupper($domain));

    expect($canonicalEmail->email())->toBe('john.doe@' . $domain)
        ->and($canonicalEmail->provider?->name())->toBe('proton');
})->with(['proton.me', 'protonmail.com', 'protonmail.ch', 'pm.me']);
