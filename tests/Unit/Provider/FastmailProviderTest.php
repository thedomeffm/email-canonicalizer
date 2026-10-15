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

test('it supports the official fastmail domains and one subdomain level', function (string $domain, bool $expected): void {
    expect((new FastmailProvider())->supports($domain))->toBe($expected);
})->with([
    'fastmail.com' => ['fastmail.com', true],
    'subdomain' => ['john.fastmail.com', true],
    'subdomain with hyphen' => ['john-doe.fastmail.com', true],
    'two subdomain levels are not supported' => ['news.john.fastmail.com', false],
    'underscore is not allowed in the alias' => ['john_doe.fastmail.com', false],
    'fastmail.fm' => ['fastmail.fm', true],
    'global non-fastmail brand' => ['sent.com', true],
    'eu region domain' => ['fastmail.co.uk', true],
    'us region domain' => ['h-mail.us', true],
    'subdomain of other fastmail domain' => ['john.fastmail.fm', true],
    'subdomain of three-label domain' => ['john.fastmail.co.uk', true],
    'pobox domain is left out' => ['pobox.com', false],
    'subdomain of pobox domain is left out' => ['john.pobox.com', false],
    'undocumented domain' => ['mailandftp.com', false],
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

test('it keeps each fastmail domain and does not merge them', function (string $email, string $expected): void {
    $canonicalEmail = (new EmailCanonicalizer(new FastmailProvider()))->canonicalize($email);

    expect($canonicalEmail->email())->toBe($expected);
})->with([
    'plus addressing' => ['john+shopping@fastmail.fm', 'john@fastmail.fm'],
    'subdomain addressing' => ['shopping@john.fastmail.fm', 'john@fastmail.fm'],
    'three-label domain' => ['shopping@john.fastmail.co.uk', 'john@fastmail.co.uk'],
    'non-fastmail brand' => ['john+shopping@sent.com', 'john@sent.com'],
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
