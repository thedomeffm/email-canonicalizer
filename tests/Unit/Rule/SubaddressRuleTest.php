<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

test('it implements the local part rule interface', function (): void {
    expect(new SubaddressRule())->toBeInstanceOf(LocalPartRuleInterface::class);
});

test('it removes the subaddress', function (string $localPart, string $expected): void {
    expect((new SubaddressRule())->apply($localPart))->toBe($expected);
})->with([
    'no subaddress' => ['john.doe', 'john.doe'],
    'tag' => ['john.doe+newsletter', 'john.doe'],
    'empty tag' => ['john.doe+', 'john.doe'],
    'several separators' => ['john+a+b', 'john'],
    'dots inside the tag' => ['john+news.letter', 'john'],
]);

test('it keeps the local part when it starts with the separator', function (): void {
    expect((new SubaddressRule())->apply('+tag'))->toBe('+tag');
});

test('it supports other separators', function (): void {
    $subaddressRule = new SubaddressRule('-');

    expect($subaddressRule->apply('john-newsletter'))->toBe('john')
        ->and($subaddressRule->apply('john+newsletter'))->toBe('john+newsletter');
});

test('it supports separators longer than one character', function (): void {
    expect((new SubaddressRule('--'))->apply('john-doe--newsletter'))->toBe('john-doe');
});

test('it rejects an empty separator', function (): void {
    new SubaddressRule('');
})->throws(InvalidArgumentException::class);
