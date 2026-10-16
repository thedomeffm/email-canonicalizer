# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`thedomeffm/email-canonicalizer` is a PHP library that turns addresses at public email providers into a canonical form, so that variants of the same mailbox (e.g. `john.doe@gmail.com`, `john.doe@googlemail.com`, `john.doe+whatever@gmail.com`) count as one. Apps use it to stop one person from signing up for several accounts with the same mailbox. Do not use the canonical address for login or for sending mail (the README says so).

The repo is at an early stage and is developed test-first (TDD): write the failing Pest test before the implementation. Keep the README's Usage example working when the public API changes.

## Architecture

- `EmailCanonicalizer::canonicalize()` parses the input with `EmailAddress::fromString()`: it trims, splits at the last `@`, rejects quoted local parts, lowercases the domain (internationalized domains go through `idn_to_ascii`; without `ext-intl` that throws `Exception\MissingExtensionException`) and validates (throwing `Exception\InvalidEmailException`). **The local part keeps its case** until a provider matches: then `EmailCanonicalizer` lowercases it with `mb_strtolower` before the rules.
- A provider is configuration only (`Provider\ProviderInterface`: `name()`, `domainMatcher()`, `rules()`), with no base class and no logic. `EmailCanonicalizer` does the work: the first provider whose `Matcher\DomainMatcherInterface` matches the domain wins, the local part is lowercased with `mb_strtolower`, then the rules apply in order. A rule result with an empty local part is skipped.
- The constructor is a typed variadic `ProviderInterface ...$providers`. It throws on no providers and on a provider with an `AnyDomainMatcher` that isn't last. `EmailCanonicalizer::default(...$customProviders)` means custom providers, then `PublicMailProviders`, then `LowercaseProvider`. PHP doesn't allow a positional argument after a spread, so `default()` builds an array first.
- Matchers (`src/Matcher/`): `ExactDomainMatcher` (listed domains), `FastmailDomainMatcher` (official list plus one subdomain level; holds the list as `DOMAINS`), `AnyDomainMatcher` (catch-all).
- Rules (`src/Rule/`, `RuleInterface`: `EmailAddress` in, `EmailAddress` out): `SubaddressRule` cuts off `+tag` (the separator is configurable), `RemoveCharactersRule` removes characters (Gmail's dots), `RewriteDomainRule` rewrites alias domains to one target, `SubdomainAddressingRule` moves `user` of `tag@user.example.com` into the local part. Domains that must be rewritten are listed twice, in the matcher and in the `RewriteDomainRule`; the provider tests cover both.
- `PublicMailProviders` (an `IteratorAggregate` in `src/`) lists every built-in provider except `LowercaseProvider`, the catch-all fallback. `Provider\GenericProvider` builds a provider in one line for custom use.
- The result is an immutable `CanonicalEmail` that holds the original input, the canonical parts and the matched provider, or `null` if no provider matched (possible without a `LowercaseProvider`).
- Each built-in provider is its own `final readonly` class that implements `ProviderInterface` and returns its data. There is deliberately no free-form alias map: every rewrite goes to one target domain.
- Tests use `tests/Fixtures/StripDotsProvider` as a stand-in provider and `tests/Fixtures/Canonical` to canonicalize with a single provider.

## Provider documentation

`docs/providers.md` is the evidence behind every provider and must stay in sync with the code:

- Only add a domain, alias or rule when the provider's **official** documentation backs it. Record the source link in `docs/providers.md`, and link that section from the provider class's docblock. The only exception is an alias domain verified by a real delivery test, marked as such with its date in `docs/providers.md` (so far only `googlemail.com`). Never add one based on user reports or other libraries alone.
- Similar-looking domains are not automatically one mailbox. Only make a domain an alias domain when the provider documents that it reaches the same mailbox; otherwise it's an additional domain. E.g. Proton's `protonmail.com`, `protonmail.ch` and `pm.me` support `+tag` but are additional domains, not aliases of `proton.me`.
- When adding, changing or removing a provider, update `docs/providers.md` and the Supported Providers table in `README.md` together.
- New provider classes must also be added to `PublicMailProviders`; `tests/Unit/PublicMailProvidersTest.php` fails otherwise.
- The canonical output is stored by users, so a changed rule or domain is a breaking change (see the Versioning section in `README.md`). There is deliberately no changelog file: list every added provider and every changed rule or domain in the GitHub release notes of the release, and release changes to existing output only as a major version.
- `tests/Unit/Provider/ProviderDocumentationTest.php` enforces this: every built-in provider in `src/Provider/` (everything instantiable except `GenericProvider`, see `tests/Fixtures/ProviderClasses.php`) needs a ``| Name | `<name>` |`` row in `docs/providers.md` and an `@see docs/providers.md#<section>` line in its class docblock.

## Constraints

- **PHP `>=8.3`** (`composer.json`). CI runs Pest on 8.3, 8.4 and 8.5, plus a lint job with `ecs check` and `rector process --dry-run` on 8.3 (`.github/workflows/tests.yml`). Library code must run on 8.3, so don't use 8.4+ features such as property hooks, asymmetric visibility, or `new` without parentheses in chains. The local CLI is 8.4, so it won't catch these.
- Pest is pinned to `^4.7.8` because Pest 5 requires PHP 8.4. Don't upgrade it while 8.3 is supported.
- No runtime dependencies except `ext-mbstring`. `ext-intl` is only suggested and checked at runtime, and only for non-ASCII domains.
- PSR-4: namespace `TheDomeFfm\EmailCanonicalizer\` maps to `src/`.
- `composer.json` sets `"lock": false`, so no `composer.lock` is committed.

## Commands

Config lives in `ecs.php`, `rector.php` and `phpunit.xml`; there is no `tests/Pest.php`. ECS and Rector check `src/`, `tests/` and the root PHP files, with their full sets turned on and no levels. Run both before committing. Tests go in `tests/Unit`, use the `Tests\` namespace (`autoload-dev`), and file names must end in `Test.php`.

```shell
composer install

vendor/bin/pest                          # run all tests
vendor/bin/pest tests/SomeTest.php       # run one file
vendor/bin/pest --filter="test name"     # run one test

vendor/bin/ecs check                     # coding standard (add --fix to apply)
vendor/bin/rector process --dry-run      # refactoring checks (drop --dry-run to apply)
```
