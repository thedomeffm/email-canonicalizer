<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;
use TheDomeFfm\EmailCanonicalizer\Exception\MissingExtensionException;

test('it trims and lowercases the domain but keeps the case of the local part', function (): void {
    $emailAddress = EmailAddress::fromString(" John.Doe@Example.COM \n");

    expect($emailAddress->localPart)->toBe('John.Doe')
        ->and($emailAddress->domain)->toBe('example.com');
});

test('it rejects invalid addresses', function (string $email): void {
    EmailAddress::fromString($email);
})->throws(InvalidEmailException::class)->with([
    'empty' => '',
    'display name' => 'John Doe <john@example.com>',
    'quoted local part' => '"john+x"@gmail.com',
    'quoted local part with dot' => '"john.doe"@gmail.com',
    'trailing dot in domain' => 'john@gmail.com.',
    'non-breaking space' => "\u{a0}john@gmail.com",
]);

test('it converts internationalized domains to punycode', function (): void {
    expect(EmailAddress::fromString('john@Müller.de')->domain)->toBe('xn--mller-kva.de')
        ->and(EmailAddress::fromString('john@xn--mller-kva.de')->domain)->toBe('xn--mller-kva.de');
})->skip(! function_exists('idn_to_ascii'), 'ext-intl is not installed');

test('it throws when an internationalized domain needs a missing extension', function (): void {
    EmailAddress::fromString('john@müller.de', idnSupported: false);
})->throws(MissingExtensionException::class);

test('it does not need intl for ascii domains', function (): void {
    expect(EmailAddress::fromString('john@xn--mller-kva.de', idnSupported: false)->domain)->toBe('xn--mller-kva.de');
});

test('it still rejects invalid input without intl', function (): void {
    EmailAddress::fromString('john@', idnSupported: false);
})->throws(InvalidEmailException::class);
