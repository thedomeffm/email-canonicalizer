<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubdomainAddressingRule;

test('it implements the rule interface', function (): void {
    expect(new SubdomainAddressingRule('example.com'))->toBeInstanceOf(RuleInterface::class);
});

test('it moves the subdomain into the local part and drops the old local part', function (string $localPart): void {
    $emailAddress = (new SubdomainAddressingRule('example.com'))->apply(new EmailAddress($localPart, 'john.example.com'));

    expect($emailAddress->localPart)->toBe('john')
        ->and($emailAddress->domain)->toBe('example.com');
})->with(['newsletter', 'news.letter', 'news+letter']);

test('it keeps the base domains and unknown domains', function (string $domain): void {
    $emailAddress = (new SubdomainAddressingRule('example.com', 'example.co.uk'))->apply(new EmailAddress('john', $domain));

    expect($emailAddress->localPart)->toBe('john')
        ->and($emailAddress->domain)->toBe($domain);
})->with(['example.com', 'example.co.uk', 'other.org', 'news.john.example.com']);

test('it works with base domains that have several labels', function (): void {
    $emailAddress = (new SubdomainAddressingRule('example.co.uk'))->apply(new EmailAddress('news', 'john.example.co.uk'));

    expect($emailAddress->localPart)->toBe('john')
        ->and($emailAddress->domain)->toBe('example.co.uk');
});

test('it needs at least one base domain', function (): void {
    new SubdomainAddressingRule();
})->throws(InvalidArgumentException::class);
