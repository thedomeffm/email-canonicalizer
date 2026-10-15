<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailAddress;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;

test('it implements the rule interface', function (): void {
    expect(new RemoveCharactersRule('.'))->toBeInstanceOf(RuleInterface::class);
});

test('it removes characters from the local part only', function (string $localPart, string $expected): void {
    $emailAddress = (new RemoveCharactersRule('.'))->apply(new EmailAddress($localPart, 'example.com'));

    expect($emailAddress->localPart)->toBe($expected)
        ->and($emailAddress->domain)->toBe('example.com');
})->with([
    'no dots' => ['johndoe', 'johndoe'],
    'one dot' => ['john.doe', 'johndoe'],
    'several dots' => ['j.o.h.n.doe', 'johndoe'],
    'consecutive dots' => ['john..doe', 'johndoe'],
]);

test('it removes several different characters', function (): void {
    $emailAddress = (new RemoveCharactersRule('.', '_'))->apply(new EmailAddress('john.doe_smith', 'example.com'));

    expect($emailAddress->localPart)->toBe('johndoesmith');
});

test('it rejects empty characters', function (): void {
    new RemoveCharactersRule('.', '');
})->throws(InvalidArgumentException::class);

test('it requires at least one character', function (): void {
    new RemoveCharactersRule();
})->throws(InvalidArgumentException::class);
