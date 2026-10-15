<?php

declare(strict_types=1);

use Tests\Fixtures\ExampleProvider;
use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\CharacterRule;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

test('it implements the provider interface', function (): void {
    expect(new ExampleProvider())->toBeInstanceOf(ProviderInterface::class);
});

test('it supports its domains and aliases', function (string $domain, bool $expected): void {
    expect((new ExampleProvider())->supports($domain))->toBe($expected);
})->with([
    'primary domain' => ['example.com', true],
    'additional domain' => ['example.org', true],
    'first alias' => ['example.net', true],
    'second alias' => ['example.info', true],
    'other domain' => ['example.de', false],
    'subdomain' => ['mail.example.com', false],
]);

test('it keeps the primary and additional domains', function (string $domain): void {
    $emailAddress = (new ExampleProvider())->applyRules(new EmailAddress('john', $domain));

    expect($emailAddress->domain)->toBe($domain);
})->with(['example.com', 'example.org']);

test('it rewrites every alias to the primary domain', function (string $domain): void {
    $emailAddress = (new ExampleProvider())->applyRules(new EmailAddress('john', $domain));

    expect($emailAddress->domain)->toBe('example.com');
})->with(['example.net', 'example.info']);

test('it applies the rules in order', function (): void {
    $emailAddress = (new ExampleProvider())->applyRules(new EmailAddress('j.o.hn+news.letter', 'example.com'));

    expect($emailAddress->localPart)->toBe('john');
});

test('it skips a rule that would empty the local part', function (): void {
    $exampleProvider = new ExampleProvider([new SubaddressRule(), new CharacterRule(remove: ['-'])]);

    $emailAddress = $exampleProvider->applyRules(new EmailAddress('-+tag', 'example.com'));

    expect($emailAddress->localPart)->toBe('-');
});
