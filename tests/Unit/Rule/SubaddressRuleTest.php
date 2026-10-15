<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

test('it implements the rule interface', function (): void {
    expect(new SubaddressRule())->toBeInstanceOf(RuleInterface::class);
});

test('it removes the subaddress', function (string $localPart, string $expected): void {
    $emailAddress = (new SubaddressRule())->apply(new EmailAddress($localPart, 'example.com'));

    expect($emailAddress->localPart)->toBe($expected)
        ->and($emailAddress->domain)->toBe('example.com');
})->with([
    'no subaddress' => ['john.doe', 'john.doe'],
    'tag' => ['john.doe+newsletter', 'john.doe'],
    'empty tag' => ['john.doe+', 'john.doe'],
    'several separators' => ['john+a+b', 'john'],
    'dots inside the tag' => ['john+news.letter', 'john'],
]);

test('it keeps the local part when it starts with the separator', function (): void {
    expect((new SubaddressRule())->apply(new EmailAddress('+tag', 'example.com'))->localPart)->toBe('+tag');
});

test('it supports other separators', function (): void {
    $subaddressRule = new SubaddressRule('-');

    expect($subaddressRule->apply(new EmailAddress('john-newsletter', 'example.com'))->localPart)->toBe('john')
        ->and($subaddressRule->apply(new EmailAddress('john+newsletter', 'example.com'))->localPart)->toBe('john+newsletter');
});

test('it supports separators longer than one character', function (): void {
    expect((new SubaddressRule('--'))->apply(new EmailAddress('john-doe--newsletter', 'example.com'))->localPart)->toBe('john-doe');
});

test('it rejects an empty separator', function (): void {
    new SubaddressRule('');
})->throws(InvalidArgumentException::class);
