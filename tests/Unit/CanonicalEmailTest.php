<?php

declare(strict_types=1);

use Tests\Fixtures\StripDotsProvider;
use TheDomeFfm\EmailCanonicalizer\CanonicalEmail;

test('it builds the email from local part and domain', function (): void {
    $canonicalEmail = new CanonicalEmail('John.Doe@Example.com', 'john.doe', 'example.com', null);

    expect($canonicalEmail->email())->toBe('john.doe@example.com')
        ->and((string) $canonicalEmail)->toBe('john.doe@example.com');
});

test('it knows whether a provider matched', function (): void {
    expect((new CanonicalEmail('a@example.com', 'a', 'example.com', null))->isKnownProvider())->toBeFalse()
        ->and((new CanonicalEmail('a@example.com', 'a', 'example.com', new StripDotsProvider()))->isKnownProvider())->toBeTrue();
});
