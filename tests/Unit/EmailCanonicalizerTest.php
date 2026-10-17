<?php

declare(strict_types=1);

use Tests\Fixtures\StripDotsProvider;
use TheDomeFfm\EmailCanonicalizer\CanonicalEmail;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizerInterface;
use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\GenericProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\LowercaseProvider;
use TheDomeFfm\EmailCanonicalizer\PublicMailProviders;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

test('it implements the interface', function (): void {
    expect(EmailCanonicalizer::default())->toBeInstanceOf(EmailCanonicalizerInterface::class);
});

test('it keeps the original input', function (): void {
    $canonicalEmail = (EmailCanonicalizer::default())->canonicalize(' John.Doe@Example.COM ');

    expect($canonicalEmail->original)->toBe(' John.Doe@Example.COM ');
});

test('by default it trims and lowercases addresses at unknown domains', function (): void {
    $canonicalEmail = (EmailCanonicalizer::default())->canonicalize(" \tJohn.Doe+Tag@Example.COM \n");

    expect($canonicalEmail->localPart)->toBe('john.doe+tag')
        ->and($canonicalEmail->domain)->toBe('example.com')
        ->and($canonicalEmail->email())->toBe('john.doe+tag@example.com')
        ->and($canonicalEmail->provider)->toBeInstanceOf(LowercaseProvider::class);
});

test('it uses all public mail providers by default', function (string $email, string $expected, string $provider): void {
    $canonicalEmail = (EmailCanonicalizer::default())->canonicalize($email);

    expect($canonicalEmail->email())->toBe($expected)
        ->and($canonicalEmail->provider?->name())->toBe($provider);
})->with([
    'gmail' => ['John.Doe+news@googlemail.com', 'johndoe@gmail.com', 'gmail'],
    'outlook' => ['John.Doe+news@outlook.com', 'john.doe@outlook.com', 'outlook'],
    'proton' => ['John.Doe+news@pm.me', 'john.doe@pm.me', 'proton'],
    'fastmail' => ['news@john.fastmail.com', 'john@fastmail.com', 'fastmail'],
    'yandex' => ['John.Doe+news@ya.ru', 'john.doe@yandex.com', 'yandex'],
    'posteo' => ['John.Doe+news@Posteo.de', 'john.doe@posteo.de', 'posteo'],
    'mailbox.org' => ['John.Doe+news@mailbox.org', 'john.doe@mailbox.org', 'mailbox-org'],
    'unknown' => ['John.Doe+news@example.com', 'john.doe+news@example.com', 'lowercase'],
]);

test('public mail providers alone leave unknown domains without a provider and keep the local part case', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(...new PublicMailProviders()))->canonicalize('John.Doe@Example.COM');

    expect($canonicalEmail->provider)->toBeNull()
        ->and($canonicalEmail->email())->toBe('John.Doe@example.com');
});

test('passed providers replace the defaults', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new StripDotsProvider()))->canonicalize('john.doe@gmail.com');

    expect($canonicalEmail->provider)->toBeNull()
        ->and($canonicalEmail->email())->toBe('john.doe@gmail.com');
});

test('default adds your own providers in front of the public ones', function (): void {
    $custom = new StripDotsProvider('custom', 'example.com');
    $emailCanonicalizer = EmailCanonicalizer::default($custom);

    expect($emailCanonicalizer->canonicalize('J.D@Example.com')->provider)->toBe($custom)
        ->and($emailCanonicalizer->canonicalize('J.D@Example.com')->email())->toBe('jd@example.com')
        ->and($emailCanonicalizer->canonicalize('J.D+x@Gmail.com')->email())->toBe('jd@gmail.com')
        ->and($emailCanonicalizer->canonicalize('J.D@Other.com')->email())->toBe('j.d@other.com');
});

test('your own provider can win over a public one', function (): void {
    $custom = new StripDotsProvider('custom', 'gmail.com');

    expect(EmailCanonicalizer::default($custom)->canonicalize('J.D+x@Gmail.com')->provider)->toBe($custom);
});

test('a list of providers can be spread into the constructor', function (): void {
    $providers = [new GmailProvider(), new LowercaseProvider()];

    expect((new EmailCanonicalizer(...$providers))->canonicalize('J.D+x@Gmail.com')->email())->toBe('jd@gmail.com');
});

test('a lowercase-only canonicalizer only trims and lowercases', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new LowercaseProvider()))->canonicalize(' J.D+X@Gmail.com ');

    expect($canonicalEmail->email())->toBe('j.d+x@gmail.com');
});

test('lowercaseOnly only trims and lowercases', function (): void {
    $canonicalEmail = EmailCanonicalizer::lowercaseOnly()->canonicalize(' J.D+X@GoogleMail.com ');

    expect($canonicalEmail)->toBeInstanceOf(CanonicalEmail::class)
        ->and($canonicalEmail->email())->toBe('j.d+x@googlemail.com')
        ->and($canonicalEmail->provider?->name())->toBe('lowercase');
});

test('a matched provider gets the trimmed address with a lowercased local part and domain', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(new StripDotsProvider()))->canonicalize(' John.Doe@EXAMPLE.com ');

    expect($canonicalEmail->email())->toBe('johndoe@example.com');
});

test('the first matching provider wins', function (): void {
    $canonicalEmail = (new EmailCanonicalizer(
        new StripDotsProvider('first'),
        new StripDotsProvider('second'),
    ))->canonicalize('john.doe@example.com');

    expect($canonicalEmail->provider?->name())->toBe('first');
});

test('it rejects an empty provider list', function (): void {
    new EmailCanonicalizer();
})->throws(InvalidArgumentException::class);

test('it rejects a provider that matches every domain unless it is last', function (): void {
    new EmailCanonicalizer(new LowercaseProvider(), new GmailProvider());
})->throws(InvalidArgumentException::class);

test('it skips a rule result with an empty local part', function (): void {
    $genericProvider = new GenericProvider('custom', new ExactDomainMatcher('example.com'), new SubaddressRule(), new RemoveCharactersRule('-'));

    expect((new EmailCanonicalizer($genericProvider))->canonicalize('-+tag@example.com')->email())->toBe('-@example.com');
});

test('it lowercases the local part before the rules of a matched provider', function (): void {
    $genericProvider = new GenericProvider('custom', new ExactDomainMatcher('example.com'), new RemoveCharactersRule('.'));

    expect((new EmailCanonicalizer($genericProvider))->canonicalize('J.Ö.Hn@example.com')->email())->toBe('jöhn@example.com');
});

test('it throws on invalid email addresses', function (string $email): void {
    (EmailCanonicalizer::default())->canonicalize($email);
})->throws(InvalidEmailException::class)->with([
    'empty' => '',
    'whitespace only' => '   ',
    'no at sign' => 'john.doe',
    'empty local part' => '@example.com',
    'empty domain' => 'john.doe@',
    'two at signs' => 'john@doe@example.com',
    'space in domain' => 'john.doe@example com',
    'display name' => 'John Doe <john@gmail.com>',
    'quoted local part' => '"john+x"@gmail.com',
]);

test('it lowercases non-ascii letters in the local part', function (): void {
    expect((EmailCanonicalizer::default())->canonicalize('ÄBC@Gmail.com')->email())->toBe('äbc@gmail.com');
});

test('it merges internationalized and punycode domains', function (): void {
    $emailCanonicalizer = EmailCanonicalizer::default();

    expect($emailCanonicalizer->canonicalize('john@Müller.de')->email())->toBe($emailCanonicalizer->canonicalize('john@xn--mller-kva.de')->email());
})->skip(! function_exists('idn_to_ascii'), 'ext-intl is not installed');

test('it never merges what we deliberately do not merge', function (string $email, string $expected): void {
    expect((EmailCanonicalizer::default())->canonicalize($email)->email())->toBe($expected);
})->with([
    'hotmail plus and dots' => ['Te.st+x@hotmail.com', 'te.st+x@hotmail.com'],
    'live dots' => ['t.e.s.t@live.com', 't.e.s.t@live.com'],
    'yahoo hyphen' => ['Test-x@yahoo.com', 'test-x@yahoo.com'],
    'icloud alias' => ['u+x@me.com', 'u+x@me.com'],
    'mac alias' => ['u+x@mac.com', 'u+x@mac.com'],
    'google workspace dots' => ['t.e.s.t@google.com', 't.e.s.t@google.com'],
    'yandex.ua' => ['x@yandex.ua', 'x@yandex.ua'],
    'mail.ru plus' => ['x+y@mail.ru', 'x+y@mail.ru'],
    'outlook dots' => ['t.e.s.t@outlook.com', 't.e.s.t@outlook.com'],
    'proton domains stay apart' => ['x+y@protonmail.com', 'x@protonmail.com'],
]);

test('the output is always valid and canonicalizing it again changes nothing', function (string $email): void {
    $emailCanonicalizer = EmailCanonicalizer::default();
    $canonicalEmail = $emailCanonicalizer->canonicalize($email);
    $second = $emailCanonicalizer->canonicalize($canonicalEmail->email());

    expect(filter_var($canonicalEmail->email(), FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE))->not->toBeFalse()
        ->and($second->email())->toBe($canonicalEmail->email());
})->with([
    'johndoe@gmail.com',
    ' J.o.H.n+Shop@GoogleMail.com ',
    'ÄBC.Ö+x@gmail.com',
    'John+X@Outlook.com',
    'x@john.fastmail.com',
    'X+y@John.Fastmail.fm',
    'John.Doe@pm.me',
    'A.b+c@ya.ru',
    'Plain+Tag@Example.org',
    '+tag@gmail.com',
    'a-@gmail.com',
]);
