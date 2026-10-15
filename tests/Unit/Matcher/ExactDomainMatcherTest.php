<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;

test('it implements the matcher interface', function (): void {
    expect(new ExactDomainMatcher('gmail.com'))->toBeInstanceOf(DomainMatcherInterface::class);
});

test('it matches exactly the given domains', function (string $domain, bool $expected): void {
    expect((new ExactDomainMatcher('gmail.com', 'googlemail.com'))->matches($domain))->toBe($expected);
})->with([
    'first domain' => ['gmail.com', true],
    'second domain' => ['googlemail.com', true],
    'other tld' => ['gmail.de', false],
    'subdomain' => ['mail.gmail.com', false],
    'suffix only' => ['notgmail.com', false],
    'empty' => ['', false],
]);

test('it needs at least one domain', function (): void {
    new ExactDomainMatcher();
})->throws(InvalidArgumentException::class);

test('it rejects empty domains', function (): void {
    new ExactDomainMatcher('gmail.com', '');
})->throws(InvalidArgumentException::class);
