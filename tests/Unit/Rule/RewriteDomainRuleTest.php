<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\RewriteDomainRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;

test('it implements the rule interface', function (): void {
    expect(new RewriteDomainRule('gmail.com', ['googlemail.com']))->toBeInstanceOf(RuleInterface::class);
});

test('it rewrites the given domains to one target domain', function (string $domain, string $expected): void {
    $rewriteDomainRule = new RewriteDomainRule('example.com', ['example.net', 'example.info']);

    $emailAddress = $rewriteDomainRule->apply(new EmailAddress('john', $domain));

    expect($emailAddress->domain)->toBe($expected)
        ->and($emailAddress->localPart)->toBe('john');
})->with([
    'first' => ['example.net', 'example.com'],
    'second' => ['example.info', 'example.com'],
    'target stays' => ['example.com', 'example.com'],
    'other domain stays' => ['example.org', 'example.org'],
]);

test('it needs at least one domain to rewrite', function (): void {
    new RewriteDomainRule('example.com', []);
})->throws(InvalidArgumentException::class);

test('it rejects empty domains', function (string $target, string $from): void {
    new RewriteDomainRule($target, [$from]);
})->throws(InvalidArgumentException::class)->with([
    'empty target' => ['', 'example.net'],
    'empty source' => ['example.com', ''],
]);
