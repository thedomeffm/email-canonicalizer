<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Provider\OutlookProvider;

test('it is named outlook', function (): void {
    expect((new OutlookProvider())->name())->toBe('outlook');
});

test('it supports only outlook.com', function (string $domain, bool $expected): void {
    expect((new OutlookProvider())->supports($domain))->toBe($expected);
})->with([
    'outlook.com' => ['outlook.com', true],
    'hotmail.com is not documented' => ['hotmail.com', false],
    'live.com is not documented' => ['live.com', false],
    'msn.com is not documented' => ['msn.com', false],
    'country domain is not documented' => ['outlook.de', false],
    'subdomain' => ['mail.outlook.com', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    $emailAddress = (new OutlookProvider())->applyRules(new EmailAddress($localPart, 'outlook.com'));

    expect($emailAddress->localPart)->toBe($expected)
        ->and($emailAddress->domain)->toBe('outlook.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it canonicalizes outlook addresses end to end', function (string $email): void {
    $canonicalEmail = (new EmailCanonicalizer(new OutlookProvider()))->canonicalize($email);

    expect($canonicalEmail->email())->toBe('john.doe@outlook.com')
        ->and($canonicalEmail->provider?->name())->toBe('outlook');
})->with([
    'john.doe@outlook.com',
    'John.Doe@Outlook.com',
    ' john.doe+shopping@OUTLOOK.COM ',
]);
