<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\GenericProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RewriteDomainRule;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

test('it implements the provider interface', function (): void {
    expect(new GenericProvider('custom', new ExactDomainMatcher('example.com')))->toBeInstanceOf(ProviderInterface::class);
});

test('it returns what it was built with', function (): void {
    $exactDomainMatcher = new ExactDomainMatcher('example.com');
    $subaddressRule = new SubaddressRule('-');

    $genericProvider = new GenericProvider('custom', $exactDomainMatcher, $subaddressRule);

    expect($genericProvider->name())->toBe('custom')
        ->and($genericProvider->domainMatcher())->toBe($exactDomainMatcher)
        ->and($genericProvider->rules())->toBe([$subaddressRule]);
});

test('it works without rules', function (): void {
    expect((new GenericProvider('custom', new ExactDomainMatcher('example.com')))->rules())->toBe([]);
});

test('it rejects an empty name', function (): void {
    new GenericProvider('', new ExactDomainMatcher('example.com'));
})->throws(InvalidArgumentException::class);

test('it canonicalizes with its rules in order', function (): void {
    $genericProvider = new GenericProvider(
        'custom',
        new ExactDomainMatcher('example.com', 'example.net'),
        new SubaddressRule('-'),
        new RemoveCharactersRule('.'),
        new RewriteDomainRule('example.com', ['example.net']),
    );

    expect(Canonical::email($genericProvider, 'Jo.Hn-Shop@Example.NET'))->toBe('john@example.com');
});
