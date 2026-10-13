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

| Provider | Class | Primary domain | Other domains | Rules |
|---|---|---|---|---|
| Gmail | `GmailProvider` | `gmail.com` | `googlemail.com` (rewritten to `gmail.com`) | removes `+tag` and dots |
| Outlook.com | `OutlookProvider` | `outlook.com` | | removes `+tag` |
| Proton Mail | `ProtonProvider` | `proton.me` | `protonmail.com`, `protonmail.ch`, `pm.me` (kept as they are) | removes `+tag` |
| Fastmail | `FastmailProvider` | `fastmail.com` | | removes `+tag`; `tag@user.fastmail.com` becomes `user@fastmail.com` |

Only rules that the provider documents officially are applied. The one exception is `googlemail.com`, which Google doesn't document but which was verified with a real delivery test. A wrong rule would merge different people, so providers without official documentation (e.g. Hotmail, iCloud, Yahoo, GMX) are left out for now.

See [docs/providers.md](docs/providers.md) for the sources behind each rule, and for why other providers are not supported yet.

