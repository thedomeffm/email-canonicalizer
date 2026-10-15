<?php

declare(strict_types=1);

use Tests\Fixtures\ProviderClasses;
use TheDomeFfm\EmailCanonicalizer\Provider\LowercaseProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;
use TheDomeFfm\EmailCanonicalizer\PublicMailProviders;

test('it yields provider instances', function (): void {
    $providers = iterator_to_array(new PublicMailProviders(), false);

    expect($providers)->not->toBeEmpty()
        ->each->toBeInstanceOf(ProviderInterface::class);
});

test('it can be iterated more than once', function (): void {
    $publicMailProviders = new PublicMailProviders();

    expect(iterator_to_array($publicMailProviders, false))->toHaveSameSize(iterator_to_array($publicMailProviders, false));
});

test('it includes every provider class except the lowercase fallback', function (): void {
    $included = array_map(get_class(...), iterator_to_array(new PublicMailProviders(), false));
    $expected = array_values(array_filter(
        array_map(
            static fn (ReflectionClass $reflectionClass): string => $reflectionClass->getName(),
            ProviderClasses::concrete(),
        ),
        static fn (string $class): bool => $class !== LowercaseProvider::class,
    ));

    sort($included);
    sort($expected);

    expect($included)->toBe($expected);
});
