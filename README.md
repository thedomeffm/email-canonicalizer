# Email Canonicalizer

This library will canonicalize public email service providers addresses.
Most common example are Gmail addresses. Example: `john.doe@gmail.com` can also use `john.doe@googlemail.com` or `john.doe+whatever@gmail.com`.
You should consider this library when you want to limit multiple accounts in your app via the same email.

> [!IMPORTANT]
> Never use the canonicalized email address for login or communication!

### Installation

```shell script
composer require thedomeffm/email-canonicalizer
```

### Usage

```php
use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;

$canonicalizer = new EmailCanonicalizer(); // uses all supported providers

try {
    $canonicalEmail = $canonicalizer->canonicalize(' John.Doe+Shopping@GoogleMail.com ');
} catch (InvalidEmailException $exception) {
    // not a valid email address
}

$canonicalEmail->email();           // "johndoe@gmail.com", store this to detect duplicate accounts
$canonicalEmail->original;          // " John.Doe+Shopping@GoogleMail.com "
$canonicalEmail->provider?->name(); // "gmail", or null if no provider matched
$canonicalEmail->isKnownProvider(); // true
```

Addresses at unknown domains are only trimmed and lowercased: `John.Doe@Example.com` becomes `john.doe@example.com`.

To support further providers, implement `ProviderInterface` or extend `AbstractProvider`. Passed providers replace the built-in ones, so add the defaults if you want to keep them:

```php
$canonicalizer = new EmailCanonicalizer(...EmailCanonicalizer::defaultProviders(), new MyProvider());
```

### Supported Providers

Every address is trimmed and lowercased first. On top of that, these provider rules apply:

| Provider | Class | Primary domain | Other domains | Removes `+tag` | Removes dots | Subdomain addressing |
|---|---|---|---|---|---|---|
| Gmail | `GmailProvider` | `gmail.com` | `googlemail.com` (rewritten to `gmail.com`) | yes | yes | no |
| Outlook.com | `OutlookProvider` | `outlook.com` | | yes | no | no |
| Proton Mail | `ProtonProvider` | `proton.me` | `protonmail.com`, `protonmail.ch`, `pm.me` (kept as they are) | yes | no | no |
| Fastmail | `FastmailProvider` | `fastmail.com` | 115 more official Fastmail domains, e.g. `fastmail.fm`, `sent.com` (kept as they are) | yes | no | yes: `tag@user.fastmail.com` becomes `user@fastmail.com` |
| Yandex | `YandexProvider` | `yandex.com` | `yandex.ru`, `yandex.by`, `yandex.kz`, `ya.ru` (rewritten to `yandex.com`) | yes | no | no |

Only rules that the provider documents officially are applied. The one exception is `googlemail.com`, which Google doesn't document but which was verified with a real delivery test.

When we are not sure, we prefer a possible duplicate account over a wrong canonical address: a missing rule only lets a duplicate slip through, but a wrong rule merges two different people and blocks a real user. That's why providers without official documentation (e.g. Hotmail, iCloud, Yahoo, GMX) are left out for now.

> [!NOTE]
> This list is outdated the moment a version is released, because providers change their domains and rules without notice. We try to keep it up to date as far as possible. If you notice a change, please open an issue or pull request with a link to the official source.

See [docs/providers.md](docs/providers.md) for an overview of all rules with their official sources, for providers that are not supported yet, and for rules other libraries apply that we deliberately don't.
