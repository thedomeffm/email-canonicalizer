<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;

test('it is named gmail', function (): void {
    expect((new GmailProvider())->name())->toBe('gmail');
});

test('it supports gmail.com and googlemail.com', function (string $domain, bool $expected): void {
    expect((new GmailProvider())->supports($domain))->toBe($expected);
})->with([
    'gmail.com' => ['gmail.com', true],
    'googlemail.com' => ['googlemail.com', true],
    'subdomain' => ['mail.gmail.com', false],
    'other tld' => ['gmail.de', false],
]);

test('it removes the subaddress and dots', function (string $localPart, string $expected): void {
    $emailAddress = (new GmailProvider())->applyRules(new EmailAddress($localPart, 'gmail.com'));

    expect($emailAddress->localPart)->toBe($expected)
        ->and($emailAddress->domain)->toBe('gmail.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots' => ['j.o.h.n.doe', 'johndoe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'johndoe'],
    'dots inside the subaddress' => ['john.doe+news.letter', 'johndoe'],
]);

test('it rewrites googlemail.com to gmail.com', function (): void {
    $emailAddress = (new GmailProvider())->applyRules(new EmailAddress('john.doe+newsletter', 'googlemail.com'));

    expect($emailAddress->localPart)->toBe('johndoe')
        ->and($emailAddress->domain)->toBe('gmail.com');
});

test('it canonicalizes gmail addresses end to end', function (string $email): void {
    $canonicalEmail = (new EmailCanonicalizer(new GmailProvider()))->canonicalize($email);

    expect($canonicalEmail->email())->toBe('johndoe@gmail.com')
        ->and($canonicalEmail->provider?->name())->toBe('gmail');
})->with([
    'johndoe@gmail.com',
    'John.Doe@Gmail.com',
    ' j.o.h.n.d.o.e+shopping@GMAIL.COM ',
    'john.doe@googlemail.com',
    'John.Doe+News@GoogleMail.com',
]);
