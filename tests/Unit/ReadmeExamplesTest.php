<?php

declare(strict_types=1);

use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;

/**
 * The examples of the README section "Examples". Keep both in sync: the second test fails
 * when an address of this list is missing from the README.
 *
 * @return array<string, array{string, string, string}> input, canonical email, provider name
 */
function readmeExamples(): array
{
    return [
        'gmail: dots, tag and alias domain' => ['J.o.h.n.Doe+Shopping@GoogleMail.com', 'johndoe@gmail.com', 'gmail'],
        'outlook: tag, dots stay' => ['John.Doe+Shopping@outlook.com', 'john.doe@outlook.com', 'outlook'],
        'proton: tag, dots, underscores and hyphens' => ['John.Doe_x-y+Shopping@proton.me', 'johndoexy@proton.me', 'proton'],
        'fastmail: subdomain addressing' => ['shopping@john.fastmail.com', 'john@fastmail.com', 'fastmail'],
        'fastmail: tag on another domain' => ['John+Shopping@fastmail.fm', 'john@fastmail.fm', 'fastmail'],
        'yandex: alias domain' => ['John.Doe+Shopping@yandex.ru', 'john.doe@yandex.com', 'yandex'],
        'posteo: filter address' => ['John.Doe+shopping@posteo.net', 'john.doe@posteo.net', 'posteo'],
        'mailbox.org: mail extension' => ['Me+Test@mailbox.org', 'me@mailbox.org', 'mailbox-org'],
        'unknown domain: only lowercased' => ['John.Doe+Shopping@Example.com', 'john.doe+shopping@example.com', 'lowercase'],
        'custom domain at Google Workspace: tag and dots count' => ['john.doe+shopping@company.example', 'john.doe+shopping@company.example', 'lowercase'],
        'hotmail: not documented, tag stays' => ['John+Shopping@hotmail.com', 'john+shopping@hotmail.com', 'lowercase'],
        'yahoo: hyphen is part of the name' => ['max-muster@yahoo.de', 'max-muster@yahoo.de', 'lowercase'],
        'icloud: me.com is not merged with icloud.com' => ['john@me.com', 'john@me.com', 'lowercase'],
        'proton: domains are not merged' => ['john+shopping@protonmail.com', 'john@protonmail.com', 'proton'],
    ];
}

test('the README examples produce the documented canonical addresses', function (string $email, string $expected, string $provider): void {
    $canonicalEmail = EmailCanonicalizer::default()->canonicalize($email);

    expect($canonicalEmail->email())->toBe($expected)
        ->and($canonicalEmail->provider?->name())->toBe($provider);
})->with(readmeExamples());

test('every README example is printed in the README', function (): void {
    $readme = file_get_contents(__DIR__ . '/../../README.md');

    foreach (readmeExamples() as [$email, $expected, $provider]) {
        expect($readme)->toContain('`' . $email . '`')
            ->and($readme)->toContain('`' . $expected . '`')
            ->and($readme)->toContain('`' . $provider . '`');
    }
});
