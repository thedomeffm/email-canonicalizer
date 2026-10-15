# Changelog

The canonical address is meant to be stored, so its stability is part of the API:

- For an existing provider class, a change that alters the canonical address of any input (a new rule, a new or rewritten domain, different lowercasing) is a **breaking change** and only comes with a new major version.
- `PublicMailProviders` (and so `EmailCanonicalizer::default()`) may gain providers in minor versions. That changes the canonical address of inputs at those domains from "only lowercased" to the provider's rules. If you need frozen keys, pass an explicit provider list, e.g. `new EmailCanonicalizer(new GmailProvider(), new LowercaseProvider())`.
- Every such change is listed here.

## Unreleased

First public version.

- Providers: Gmail, Outlook.com, Proton Mail, Fastmail, Yandex, Posteo, mailbox.org, plus the `LowercaseProvider` fallback.
- `EmailCanonicalizer::default(...$customProviders)` and `new EmailCanonicalizer(ProviderInterface ...$providers)`. Providers are configuration (name, `DomainMatcherInterface`, rules); the canonicalizer applies them.
- Requires `ext-mbstring`. `ext-intl` is optional and only needed for internationalized domains.
