<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Provider\FastmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new FastmailProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named fastmail', function (): void {
    expect((new FastmailProvider())->name())->toBe('fastmail');
});

test('it supports fastmail.com and one subdomain level', function (string $domain, bool $expected): void {
    expect((new FastmailProvider())->supports($domain))->toBe($expected);
})->with([
    'fastmail.com' => ['fastmail.com', true],
    'subdomain' => ['john.fastmail.com', true],
    'subdomain with hyphen' => ['john-doe.fastmail.com', true],
    'two subdomain levels are not supported' => ['news.john.fastmail.com', false],
    'underscore is not allowed in the alias' => ['john_doe.fastmail.com', false],
    'other fastmail domain is not documented' => ['fastmail.fm', false],
    'similar domain' => ['notfastmail.com', false],
    'empty subdomain' => ['.fastmail.com', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    $emailAddress = (new FastmailProvider())->applyRules(new EmailAddress($localPart, 'fastmail.com'));

    expect($emailAddress->localPart)->toBe($expected)
        ->and($emailAddress->domain)->toBe('fastmail.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['johndoe+newsletter', 'johndoe'],
    'dots and subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it moves the subdomain into the local part', function (string $localPart): void {
    $emailAddress = (new FastmailProvider())->applyRules(new EmailAddress($localPart, 'john.fastmail.com'));

    expect($emailAddress->localPart)->toBe('john')
        ->and($emailAddress->domain)->toBe('fastmail.com');
})->with([
    'newsletter',
    'news.letter',
    'news+letter',
]);

test('it canonicalizes fastmail addresses end to end', function (string $email): void {
    $canonicalEmail = (new EmailCanonicalizer(new FastmailProvider()))->canonicalize($email);

    expect($canonicalEmail->email())->toBe('john@fastmail.com')
        ->and($canonicalEmail->provider?->name())->toBe('fastmail');
})->with([
    'john@fastmail.com',
    'John+Shopping@FastMail.com',
    ' Shopping@John.FastMail.com ',
]);
