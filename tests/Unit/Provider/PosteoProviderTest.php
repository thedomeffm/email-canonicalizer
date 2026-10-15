<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\PosteoProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new PosteoProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named posteo', function (): void {
    expect((new PosteoProvider())->name())->toBe('posteo');
});

test('it supports the thirteen posteo registration domains', function (string $domain, bool $expected): void {
    expect((new PosteoProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'posteo.de' => ['posteo.de', true],
    'posteo.com' => ['posteo.com', true],
    'posteo.net' => ['posteo.net', true],
    'posteo.us' => ['posteo.us', true],
    'posteo.ca' => ['posteo.ca', true],
    'posteo.co.uk is an alias-only domain' => ['posteo.co.uk', false],
    'posteo.uk' => ['posteo.uk', true],
    'posteo.ie' => ['posteo.ie', true],
    'posteo.es' => ['posteo.es', true],
    'posteo.at' => ['posteo.at', true],
    'posteo.ch' => ['posteo.ch', true],
    'posteo.nl' => ['posteo.nl', true],
    'posteo.be' => ['posteo.be', true],
    'posteo.lu' => ['posteo.lu', true],
    'alias-only domain' => ['posteo.eu', false],
    'subdomain' => ['mail.posteo.de', false],
]);

test('it removes the subaddress and keeps dots', function (string $localPart, string $expected): void {
    expect(Canonical::email(new PosteoProvider(), $localPart . '@posteo.de'))->toBe($expected . '@posteo.de');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'subaddress' => ['john.doe+newsletter', 'john.doe'],
    'several filter words' => ['john+shopping+newsletter', 'john'],
]);

test('it keeps each domain and does not merge them', function (string $domain): void {
    expect(Canonical::email(new PosteoProvider(), 'John.Doe+shopping@' . strtoupper($domain)))->toBe('john.doe@' . $domain);
})->with(['posteo.de', 'posteo.com', 'posteo.lu']);
