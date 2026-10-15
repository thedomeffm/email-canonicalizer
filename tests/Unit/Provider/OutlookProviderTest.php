<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\OutlookProvider;

test('it is named outlook', function (): void {
    expect((new OutlookProvider())->name())->toBe('outlook');
});

test('it matches only outlook.com', function (string $domain, bool $expected): void {
    expect((new OutlookProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'outlook.com' => ['outlook.com', true],
    'hotmail.com is not documented' => ['hotmail.com', false],
    'live.com is not documented' => ['live.com', false],
    'msn.com is not documented' => ['msn.com', false],
    'country domain is not documented' => ['outlook.de', false],
    'subdomain' => ['mail.outlook.com', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    expect(Canonical::email(new OutlookProvider(), $localPart . '@outlook.com'))->toBe($expected . '@outlook.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it canonicalizes outlook addresses end to end', function (string $email): void {
    expect(Canonical::email(new OutlookProvider(), $email))->toBe('john.doe@outlook.com');
})->with([
    'john.doe@outlook.com',
    'John.Doe@Outlook.com',
    ' john.doe+shopping@OUTLOOK.COM ',
]);
