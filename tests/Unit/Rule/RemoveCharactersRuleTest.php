<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\Rule\CharacterRule;
use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;

test('it implements the local part rule interface', function (): void {
    expect(new CharacterRule())->toBeInstanceOf(LocalPartRuleInterface::class);
});

test('it changes nothing without configuration', function (): void {
    expect((new CharacterRule())->apply('john.doe-smith'))->toBe('john.doe-smith');
});

test('it removes characters', function (string $localPart, string $expected): void {
    expect((new CharacterRule(remove: ['.']))->apply($localPart))->toBe($expected);
})->with([
    'no dots' => ['johndoe', 'johndoe'],
    'one dot' => ['john.doe', 'johndoe'],
    'several dots' => ['j.o.h.n.doe', 'johndoe'],
    'consecutive dots' => ['john..doe', 'johndoe'],
]);

test('it removes several different characters', function (): void {
    expect((new CharacterRule(remove: ['.', '_']))->apply('john.doe_smith'))->toBe('johndoesmith');
});

test('it replaces characters', function (): void {
    $characterRule = new CharacterRule(replace: [
        '-' => '.',
    ]);

    expect($characterRule->apply('john-doe.smith'))->toBe('john.doe.smith');
});

test('it replaces before it removes', function (): void {
    $characterRule = new CharacterRule(remove: ['.'], replace: [
        '-' => '.',
    ]);

    expect($characterRule->apply('john-doe.smith'))->toBe('johndoesmith');
});

test('it rejects empty characters to remove', function (): void {
    new CharacterRule(remove: ['']);
})->throws(InvalidArgumentException::class);

test('it rejects empty characters to replace', function (): void {
    new CharacterRule(replace: [
        '' => '.',
    ]);
})->throws(InvalidArgumentException::class);
