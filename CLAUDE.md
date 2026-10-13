# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`thedomeffm/email-canonicalizer` is a PHP library that turns addresses at public email providers into a canonical form, so that variants of the same mailbox (e.g. `john.doe@gmail.com`, `john.doe@googlemail.com`, `john.doe+whatever@gmail.com`) count as one. Apps use it to stop one person from signing up for several accounts with the same mailbox. Do not use the canonical address for login or for sending mail (the README says so).

The repo is at an early stage and is developed test-first (TDD): write the failing Pest test before the implementation. Keep the README's Usage example working when the public API changes.

## Architecture

- `EmailCanonicalizer::canonicalize()` parses the input with `EmailAddress::fromString()`, which trims and lowercases the whole address and validates it (throwing `Exception\InvalidEmailException`). Providers can rely on lowercase input.
- It then asks each provider whether it `supports()` the normalized domain. Without constructor arguments it uses `EmailCanonicalizer::defaultProviders()` (all built-in providers); passed providers replace them. The first match wins, and its `applyRules()` maps one `EmailAddress` to another, so it can change both the local part and the domain.
- The result is an immutable `CanonicalEmail` that holds the original input, the canonical parts and the matched provider, or `null` if no provider matched.
- Local-part changes are small `Rule\LocalPartRuleInterface` objects: `SubaddressRule` cuts off `+tag`, and `CharacterRule` replaces, then removes characters (e.g. Gmail's ignored dots). Providers apply them in order: subaddress first, then characters.
- Each supported provider is its own class, usually extending `Provider\AbstractProvider`. It declares a `primaryDomain()` (the provider's most common domain), optional `aliasDomains()` (rewritten to the primary domain), optional `additionalDomains()` (supported, but kept as they are) and ordered `rules()`. There is deliberately no free-form alias map: every rewrite goes to the one primary domain. `AbstractProvider` skips any rule result that would empty the local part. Providers that don't fit, like Fastmail's subdomain addressing, implement `ProviderInterface` directly.
- Tests use `tests/Fixtures/StripDotsProvider` and `tests/Fixtures/ExampleProvider` as stand-in providers.

## Provider documentation

`docs/providers.md` is the evidence behind every provider and must stay in sync with the code:

- Only add a domain, alias or rule when the provider's **official** documentation backs it. Record the source link in `docs/providers.md`, and link that section from the provider class's docblock. The only exception is an alias domain verified by a real delivery test, marked as such with its date in `docs/providers.md` (so far only `googlemail.com`). Never add one based on user reports or other libraries alone.
- Similar-looking domains are not automatically one mailbox. Only make a domain an alias domain when the provider documents that it reaches the same mailbox; otherwise it's an additional domain. E.g. Proton's `protonmail.com`, `protonmail.ch` and `pm.me` support `+tag` but are additional domains, not aliases of `proton.me`.
- When adding, changing or removing a provider, update `docs/providers.md` and the Supported Providers table in `README.md` together.
- New provider classes must also be added to `EmailCanonicalizer::defaultProviders()`; `tests/Unit/EmailCanonicalizerTest.php` fails otherwise.
- `tests/Unit/Provider/ProviderDocumentationTest.php` enforces this: every instantiable provider in `src/Provider/` needs a ``| Name | `<name>` |`` row in `docs/providers.md` and an `@see docs/providers.md#<section>` line in its class docblock.

## Constraints

- **PHP `>=8.3`** (`composer.json`). CI runs Pest on 8.3, 8.4 and 8.5, plus a lint job with `ecs check` and `rector process --dry-run` on 8.3 (`.github/workflows/tests.yml`). Library code must run on 8.3, so don't use 8.4+ features such as property hooks, asymmetric visibility, or `new` without parentheses in chains. The local CLI is 8.4, so it won't catch these.
- Pest is pinned to `^4.7.8` because Pest 5 requires PHP 8.4. Don't upgrade it while 8.3 is supported.
- No runtime dependencies. Keep it that way unless there's a strong reason.
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
