<?php

declare(strict_types=1);

use Tests\Fixtures\ProviderClasses;
use Tests\Fixtures\StripDotsProvider;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizerInterface;
use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;

test('it implements the interface', function (): void {
    expect(new EmailCanonicalizer())->toBeInstanceOf(EmailCanonicalizerInterface::class);
});

test('it keeps the original input', function (): void {
    $canonicalEmail = (new EmailCanonicalizer())->canonicalize(' John.Doe@Example.COM ');

    expect($canonicalEmail->original)->toBe(' John.Doe@Example.COM ');
});

test('it trims and lowercases the whole address', function (): void {
    $canonicalEmail = (new EmailCanonicalizer())->canonicalize(" \tJohn.Doe@Example.COM \n");

    expect($canonicalEmail->localPart)->toBe('john.doe')
        ->and($canonicalEmail->domain)->toBe('example.com')
        ->and($canonicalEmail->email())->toBe('john.doe@example.com');
});

test('providers receive the lowercased address', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new StripDotsProvider()))->canonicalize('John.Doe@EXAMPLE.com');

    expect($canonicalEmail->email())->toBe('johndoe@example.com');
});

test('it has no provider for unknown domains', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new StripDotsProvider()))->canonicalize('john.doe@unknown.org');

    expect($canonicalEmail->provider)->toBeNull()
        ->and($canonicalEmail->isKnownProvider())->toBeFalse()
        ->and($canonicalEmail->email())->toBe('john.doe@unknown.org');
});

test('it applies the rules of the matching provider', function (): void {
    $stripDotsProvider = new StripDotsProvider();

    $canonicalEmail = (new EmailCanonicalizer($stripDotsProvider))->canonicalize('john.doe@example.com');

    expect($canonicalEmail->email())->toBe('johndoe@example.com')
        ->and($canonicalEmail->provider)->toBe($stripDotsProvider)
        ->and($canonicalEmail->isKnownProvider())->toBeTrue();
});

test('the first matching provider wins', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(
        new StripDotsProvider('first'),
        new StripDotsProvider('second'),
    ))->canonicalize('john.doe@example.com');

    expect($canonicalEmail->provider?->name())->toBe('first');
});

test('it throws on invalid email addresses', function (string $email): void {
    (new EmailCanonicalizer())->canonicalize($email);
})->throws(InvalidEmailException::class)->with([
    'empty' => '',
    'whitespace only' => '   ',
    'no at sign' => 'john.doe',
    'empty local part' => '@example.com',
    'empty domain' => 'john.doe@',
    'two at signs' => 'john@doe@example.com',
    'no tld dot' => 'john.doe@example com',
]);

test('it uses all built-in providers by default', function (string $email, string $expected, string $provider): void {
    $canonicalEmail = (new EmailCanonicalizer())->canonicalize($email);

    expect($canonicalEmail->email())->toBe($expected)
        ->and($canonicalEmail->provider?->name())->toBe($provider);
})->with([
    'gmail' => ['John.Doe+news@googlemail.com', 'johndoe@gmail.com', 'gmail'],
    'outlook' => ['John.Doe+news@outlook.com', 'john.doe@outlook.com', 'outlook'],
    'proton' => ['John.Doe+news@pm.me', 'john.doe@pm.me', 'proton'],
    'fastmail' => ['news@john.fastmail.com', 'john@fastmail.com', 'fastmail'],
]);

test('the default providers include every provider class', function (): void {
    $defaultClasses = array_map(get_class(...), EmailCanonicalizer::defaultProviders());
    $providerClasses = array_map(
        static fn (ReflectionClass $reflectionClass): string => $reflectionClass->getName(),
        ProviderClasses::concrete(),
    );

    sort($defaultClasses);
    sort($providerClasses);

    expect($defaultClasses)->toBe($providerClasses);
});

test('passed providers replace the default providers', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new StripDotsProvider()))->canonicalize('john.doe@gmail.com');

    expect($canonicalEmail->provider)->toBeNull()
        ->and($canonicalEmail->email())->toBe('john.doe@gmail.com');
});
