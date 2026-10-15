<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Matcher\AnyDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\LowercaseProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

test('it implements the provider interface', function (): void {
    expect(new LowercaseProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it is named lowercase', function (): void {
    expect((new LowercaseProvider())->name())->toBe('lowercase');
});

test('it matches every domain and has no rules', function (): void {
    $lowercaseProvider = new LowercaseProvider();

    expect($lowercaseProvider->domainMatcher())->toBeInstanceOf(AnyDomainMatcher::class)
        ->and($lowercaseProvider->rules())->toBe([]);
});

test('it only trims and lowercases', function (): void {
    expect(Canonical::email(new LowercaseProvider(), ' John.Doe+News-ÄÖ@Example.COM '))->toBe('john.doe+news-äö@example.com');
});
