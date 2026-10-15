<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\Matcher\AnyDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;

test('it implements the matcher interface', function (): void {
    expect(new AnyDomainMatcher())->toBeInstanceOf(DomainMatcherInterface::class);
});

test('it matches every domain', function (string $domain): void {
    expect((new AnyDomainMatcher())->matches($domain))->toBeTrue();
})->with(['example.com', 'gmail.com', 'a.b.c.example.org', 'xn--mller-kva.de']);
