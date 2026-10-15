<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\FastmailDomainMatcher;

test('it implements the matcher interface', function (): void {
    expect(new FastmailDomainMatcher())->toBeInstanceOf(DomainMatcherInterface::class);
});

test('it matches the official fastmail domains and one subdomain level', function (string $domain, bool $expected): void {
    expect((new FastmailDomainMatcher())->matches($domain))->toBe($expected);
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
    'fastmail.ca' => ['fastmail.ca', true],
    'subdomain of other fastmail domain' => ['john.fastmail.fm', true],
    'subdomain of three-label domain' => ['john.fastmail.co.uk', true],
    'pobox domain is left out' => ['pobox.com', false],
    'subdomain of pobox domain is left out' => ['john.pobox.com', false],
    'undocumented domain' => ['mailandftp.com', false],
    'similar domain' => ['notfastmail.com', false],
    'empty subdomain' => ['.fastmail.com', false],
]);

test('it exposes the base domains for the subdomain rule', function (): void {
    expect(FastmailDomainMatcher::DOMAINS)->toContain('fastmail.com', 'sent.com', 'fastmail.co.uk')
        ->and(FastmailDomainMatcher::DOMAINS)->toHaveCount(116);
});
