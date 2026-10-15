<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\CanonicalEmail;

test('it builds the email from local part and domain', function (): void {
    $canonicalEmail = new CanonicalEmail('John.Doe@Example.com', 'john.doe', 'example.com', null);

    expect($canonicalEmail->email())->toBe('john.doe@example.com')
        ->and((string) $canonicalEmail)->toBe('john.doe@example.com');
});
