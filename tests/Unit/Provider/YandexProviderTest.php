<?php

declare(strict_types=1);

use Tests\Fixtures\Canonical;
use TheDomeFfm\EmailCanonicalizer\Provider\YandexProvider;

test('it is named yandex', function (): void {
    expect((new YandexProvider())->name())->toBe('yandex');
});

test('it matches the documented yandex domains', function (string $domain, bool $expected): void {
    expect((new YandexProvider())->domainMatcher()->matches($domain))->toBe($expected);
})->with([
    'yandex.com' => ['yandex.com', true],
    'yandex.ru' => ['yandex.ru', true],
    'yandex.by' => ['yandex.by', true],
    'yandex.kz' => ['yandex.kz', true],
    'ya.ru' => ['ya.ru', true],
    'undocumented yandex.ua' => ['yandex.ua', false],
    'subdomain' => ['mail.yandex.com', false],
]);

test('it removes the subaddress and keeps dots and hyphens', function (string $localPart, string $expected): void {
    expect(Canonical::email(new YandexProvider(), $localPart . '@yandex.com'))->toBe($expected . '@yandex.com');
})->with([
    'unchanged' => ['johndoe', 'johndoe'],
    'dots are kept' => ['john.doe', 'john.doe'],
    'hyphens are kept' => ['john-doe', 'john-doe'],
    'subaddress' => ['john.doe+newsletter', 'john.doe'],
]);

test('it rewrites the alias domains to yandex.com', function (string $domain): void {
    expect(Canonical::email(new YandexProvider(), 'John.Doe+shopping@' . strtoupper($domain)))->toBe('john.doe@yandex.com');
})->with(['yandex.com', 'yandex.ru', 'yandex.by', 'yandex.kz', 'ya.ru']);
