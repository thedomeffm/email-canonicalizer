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

$canonicalizer = EmailCanonicalizer::default(); // all public mail providers, then the lowercase fallback

try {
    $canonicalEmail = $canonicalizer->canonicalize(' John.Doe+Shopping@GoogleMail.com ');
} catch (InvalidEmailException $exception) {
    // not a valid email address
}
// MissingExtensionException (a LogicException) is only thrown for internationalized domains without ext-intl.

$canonicalEmail->email();           // "johndoe@gmail.com", store this to detect duplicate accounts
$canonicalEmail->original;          // " John.Doe+Shopping@GoogleMail.com "
$canonicalEmail->provider?->name(); // "gmail"
```

Addresses at unknown domains are only trimmed and lowercased: `John.Doe@Example.com` becomes `john.doe@example.com` (provider `lowercase`).

### Examples

Results of `EmailCanonicalizer::default()`. These are checked by `tests/Unit/ReadmeExamplesTest.php`.

**One example per provider:**

| Input | Canonical | Provider | What happens |
|---|---|---|---|
| `J.o.h.n.Doe+Shopping@GoogleMail.com` | `johndoe@gmail.com` | `gmail` | lowercased, `+tag` and dots removed, `googlemail.com` becomes `gmail.com` |
| `John.Doe+Shopping@outlook.com` | `john.doe@outlook.com` | `outlook` | lowercased, `+tag` removed, dots stay |
| `John.Doe_x-y+Shopping@proton.me` | `johndoexy@proton.me` | `proton` | lowercased, `+tag` removed, dots, underscores and hyphens removed |
| `shopping@john.fastmail.com` | `john@fastmail.com` | `fastmail` | subdomain addressing: `john` becomes the local part |
| `John+Shopping@fastmail.fm` | `john@fastmail.fm` | `fastmail` | `+tag` removed, the domain stays as it is |
| `John.Doe+Shopping@yandex.ru` | `john.doe@yandex.com` | `yandex` | `+tag` removed, `yandex.ru` becomes `yandex.com` |
| `John.Doe+shopping@posteo.net` | `john.doe@posteo.net` | `posteo` | `+filter` removed, the domain stays as it is |
| `Me+Test@mailbox.org` | `me@mailbox.org` | `mailbox-org` | `+extension` removed |
| `John.Doe+Shopping@Example.com` | `john.doe+shopping@example.com` | `lowercase` | unknown domain: only lowercased, `+tag` stays |

**Where rules deliberately do not apply:**

| Input | Canonical | Provider | Why |
|---|---|---|---|
| `john.doe+shopping@company.example` | `john.doe+shopping@company.example` | `lowercase` | A custom domain can be hosted at Google Workspace or Exchange, where dots count and admins configure `+tag`. The domain doesn't show who hosts it. |
| `John+Shopping@hotmail.com` | `john+shopping@hotmail.com` | `lowercase` | Microsoft documents `+tag` only for `outlook.com`. |
| `max-muster@yahoo.de` | `max-muster@yahoo.de` | `lowercase` | Yahoo documents no rule for `-`, and it is part of real names. |
| `john@me.com` | `john@me.com` | `lowercase` | Apple doesn't document that `me.com` and `icloud.com` are the same mailbox. |
| `john+shopping@protonmail.com` | `john@protonmail.com` | `proton` | `+tag` is removed, but `protonmail.com` is not merged into `proton.me`. |

See [docs/providers.md](docs/providers.md) for the sources.

### Choosing providers

The first provider whose domain matcher matches wins. A provider is configuration: a name, a `DomainMatcherInterface` and ordered rules. The canonicalizer lowercases the local part and applies the rules.

```php
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\GenericProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\LowercaseProvider;
use TheDomeFfm\EmailCanonicalizer\PublicMailProviders;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

EmailCanonicalizer::default();                          // public mail providers, then lowercase fallback

// your own provider in front of the defaults
EmailCanonicalizer::default(
    new GenericProvider('our-business', new ExactDomainMatcher('example.com'), new SubaddressRule('-')),
);

// only the providers you pass
new EmailCanonicalizer(new GmailProvider(), new LowercaseProvider());
new EmailCanonicalizer(...new PublicMailProviders());   // known providers only, other domains keep their local part as it is
EmailCanonicalizer::lowercaseOnly();                    // only trim and lowercase
```

- No providers at all throws, so a wrong setup can't silently do nothing.
- A provider that matches every domain (`LowercaseProvider`) hides all providers after it, so it has to be last. Otherwise the constructor throws.
- To spread your own list into the constructor: `new EmailCanonicalizer(...$providers)`. PHP doesn't allow a plain argument after a spread, so build the array first: `new EmailCanonicalizer(...[$mine, ...new PublicMailProviders(), new LowercaseProvider()])`.
- For rules that don't fit the existing ones, implement `Rule\RuleInterface` (address in, address out). For domains that don't fit, implement `Matcher\DomainMatcherInterface`. Your own `ProviderInterface` class works too: `name()`, `domainMatcher()`, `rules()`.
- Available matchers: `ExactDomainMatcher`, `FastmailDomainMatcher`, `AnyDomainMatcher`. Available rules: `SubaddressRule`, `RemoveCharactersRule`, `RewriteDomainRule`, `SubdomainAddressingRule`.

### Requirements

- PHP 8.3+ and `ext-mbstring`.
- `ext-intl` is optional. Without it, an address with an internationalized domain (`john@müller.de`) throws `MissingExtensionException`, because the domain can't be converted to punycode reliably. Addresses with ASCII or punycode domains work without it.
- Quoted local parts (`"john"@example.com`) and display names (`John <john@example.com>`) are rejected as invalid.

### Versioning

The canonical address is meant to be stored, so its stability is part of the API. There is no changelog file. Every release's GitHub release notes list each added provider and each changed rule or domain:

- A new rule or domain for an existing provider class may change the canonical address of some inputs. If it only merges more variants of the same mailbox (so the new key is still the one `canonicalize()` returns for all of them), it can come in a minor version, and the release notes list it. Anything else (a different domain, different lowercasing, splitting what was merged) only happens in a new major version.
- `PublicMailProviders`, and so `EmailCanonicalizer::default()`, may gain providers in minor versions (listed in that release's notes). If you need frozen keys, pass the providers explicitly, e.g. `new EmailCanonicalizer(new GmailProvider(), new LowercaseProvider())`.
- Store the original address as well, and re-canonicalize when you upgrade.

### Supported Providers

Every address is trimmed first and its domain is lowercased. Each provider lowercases the local part, then applies its rules:

| Provider | Class | Primary domain | Other domains | Removes `+tag` | Removes dots | Subdomain addressing |
|---|---|---|---|---|---|---|
| Gmail | `GmailProvider` | `gmail.com` | `googlemail.com` (rewritten to `gmail.com`) | yes | yes | no |
| Outlook.com | `OutlookProvider` | `outlook.com` | | yes | no | no |
| Proton Mail | `ProtonProvider` | `proton.me` | `protonmail.com`, `protonmail.ch`, `pm.me` (kept as they are) | yes | yes (also `_` and `-`) | no |
| Fastmail | `FastmailProvider` | `fastmail.com` | 115 more official Fastmail domains, e.g. `fastmail.fm`, `sent.com` (kept as they are) | yes | no | yes: `tag@user.fastmail.com` becomes `user@fastmail.com` |
| Yandex | `YandexProvider` | `yandex.com` | `yandex.ru`, `yandex.by`, `yandex.kz`, `ya.ru` (rewritten to `yandex.com`) | yes | no | no |
| Posteo | `PosteoProvider` | `posteo.de` | 12 more Posteo registration domains, e.g. `posteo.com`, `posteo.net` (kept as they are) | yes | no | no |
| mailbox.org | `MailboxOrgProvider` | `mailbox.org` | | yes | no | no |
| Lowercase | `LowercaseProvider` | every domain | fallback, always last | no | no | no |

Only rules that the provider documents officially are applied.

When we are not sure, we prefer a possible duplicate account over a wrong canonical address: a missing rule only lets a duplicate slip through, but a wrong rule merges two different people and blocks a real user. That's why providers without official documentation (e.g. Hotmail, iCloud, Yahoo, Zoho, GMX) are left out for now.

> [!NOTE]
> This list is outdated the moment a version is released, because providers change their domains and rules without notice. We try to keep it up to date as far as possible. If you notice a change, please open an issue or pull request with a link to the official source.

See [docs/providers.md](docs/providers.md) for an overview of all rules with their official sources, for providers that are not supported yet, and for rules other libraries apply that we deliberately don't.

### Alternatives

Other PHP libraries solve the same problem with different trade-offs (last commits checked on 2026-10-15):

| Library | Approach | Last commit | Notes |
|---|---|---|---|
| [sunaoka/email-normalizer-php](https://github.com/sunaoka/email-normalizer-php) | Detects the provider by DNS MX lookup, or by domain with `skipDns` | 2026-10 | Covers more providers (e.g. Apple, Zoho, Rackspace), and also recognizes custom domains hosted at a provider. The result depends on DNS and the network. |
| [rezozero/canonical-email](https://github.com/rezozero/canonical-email) | Strategies per provider (Gmail, Outlook, Google Workspace via MX lookup) | 2024-12 | Small and simple, but only a few providers. |
| [Gabrola/email-normalizer](https://github.com/Gabrola/email-normalizer) | Static rule tables per domain, no network access | 2021-11 | Many domains, but some rules merge different mailboxes, see [rules we deliberately don't apply](docs/providers.md#rules-we-deliberately-dont-apply). |

This library applies only rules that the provider documents officially, never makes network requests, and tries not to merge addresses of different people. In return, it covers fewer providers. Pick another library if you need broader coverage and accept that trade-off.
