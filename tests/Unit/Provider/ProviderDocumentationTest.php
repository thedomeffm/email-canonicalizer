<?php

declare(strict_types=1);

use Tests\Fixtures\ProviderClasses;

test('there is at least one provider to check', function (): void {
    expect(ProviderClasses::concrete())->not->toBeEmpty();
});

test('every provider is documented in docs/providers.md', function (): void {
    $documentation = (string) file_get_contents(dirname(__DIR__, 3) . '/docs/providers.md');

    foreach (ProviderClasses::concrete() as $reflectionClass) {
        $provider = $reflectionClass->newInstance();

        expect($documentation)->toContain(sprintf('| Name | `%s` |', $provider->name()));
    }
});

test('every provider links its section in docs/providers.md', function (): void {
    foreach (ProviderClasses::concrete() as $reflectionClass) {
        expect((string) $reflectionClass->getDocComment())->toContain('@see docs/providers.md#');
    }
});
