# Providers

This document is the evidence behind every provider in this library. Each provider class in `src/Provider/` links here, and every domain, alias and rule a provider applies must be listed below with its source.

Last checked: 2026-10-17.

> [!NOTE]
> This list is outdated the moment it is released. Providers add domains and change their rules without notice. We keep it up to date as well as we can. If you find a change, please open an issue or pull request with the official source.

## Overview

Which rules apply to which provider, and where the official source is. Every address is trimmed first and its domain is lowercased, whatever the provider. Each provider lowercases the local part itself.

| Provider | Status | Removes `+tag` | Removes dots | Subdomain addressing | Alias domains (rewritten) | Official sources |
|---|---|---|---|---|---|---|
| [Gmail](#gmail) | supported | yes | yes | no | `googlemail.com` | [aliases](https://support.google.com/mail/answer/22370?hl=en), [googlemail](https://support.google.com/mail/answer/10313?hl=en), [dots](https://support.google.com/mail/answer/7436150?hl=en) |
| [Outlook.com](#outlookcom) | supported | yes | no | no | none | [plus addresses](https://support.microsoft.com/en-us/outlook/send-email-from-a-different-address-in-outlook-com) |
| [Proton Mail](#proton-mail) | supported | yes | yes (also `_` and `-`) | no | none | [addresses and aliases](https://proton.me/support/addresses-and-aliases), [change username](https://proton.me/support/change-username) |
| [Fastmail](#fastmail) | supported (116 official domains) | yes | no | yes | none | [plus and subdomain addressing](https://www.fastmail.help/hc/en-us/articles/360060591053-Plus-addressing-and-subdomain-addressing), [domain list](https://www.fastmail.help/hc/en-us/articles/6239365719055-What-domains-can-I-use-with-Fastmail) |
| [Yandex](#yandex) | supported | yes | no | no | `yandex.ru`, `yandex.by`, `yandex.kz`, `ya.ru` | [plus addresses](https://yandex.com/support/mail/reg.html), [additional addresses](https://yandex.com/support/mail/web/preferences/about-sender/additional-addresses.html) |
| [Posteo](#posteo) | supported (13 domains) | yes | no | no | none | [filter addresses](https://posteo.de/en/help/filter-addresses), [domains](https://posteo.de/en/help/which-domains-are-available-to-use-as-a-posteo-alias-address) |
| [mailbox.org](#mailboxorg) | supported | yes | no | no | none | [mail extensions](https://kb.mailbox.org/en/private/e-mail/mail-extensions/) |
| [Hotmail, Live, MSN](#hotmail-live-msn-and-outlook-country-domains) | not supported | | | | | none found |
| [iCloud](#icloud) | not supported | | | | | none found for `+tag` or aliasing |
| [Yahoo](#yahoo) | not supported | | | | | none found |
| [Mail.ru](#mailru) | not supported | | | | | none found |
| [Zoho](#zoho) | not supported | | | | | none found |
| [GMX, WEB.DE, T-Online, AOL](#gmx-webde-t-online-aol) | not supported | | | | | none found |

## Rules for adding a provider

The goal is to never block a real user. **If we are not sure, we accept a possible duplicate account instead of merging two addresses into one canonical address.**

- **Only official documentation counts.** A help page or documentation from the provider itself. Blog posts, Stack Exchange answers, user reports and other libraries are not enough.
- **Domains are not merged unless documented.** Similar-looking domains of one provider can be separate mailboxes. A domain only becomes an alias domain if the provider documents that it reaches the same mailbox as the primary domain. Otherwise it is an additional domain.
- **When in doubt, leave it out.** A missing rule only lets a duplicate account slip through. A wrong rule merges different people and blocks a real user.
- **Other libraries are hints, not evidence.** They are useful to find candidates, but some of their rules are wrong, see [rules we deliberately don't apply](#rules-we-deliberately-dont-apply).

## Supported providers

The canonicalizer always trims the address and lowercases its domain first. Each provider then lowercases the local part, and applies the rules below on top of that.

In the code, a provider is a domain matcher plus rules; a `RewriteDomainRule` rewrites alias domains. Each provider has three kinds of domains:

- **Primary domain:** usually the provider's most common domain (Yandex is an exception, see [Yandex](#yandex)).
- **Alias domains:** documented to reach the same mailbox as the primary domain. They are rewritten to the primary domain.
- **Additional domains:** the provider's rules apply, but they are separate mailboxes. They are kept as they are.

### Gmail

| | |
|---|---|
| Name | `gmail` |
| Primary domain | `gmail.com` |
| Alias domains | `googlemail.com`, see below |
| Additional domains | none |
| Rules | `+tag` is removed, dots in the local part are removed |

Sources:

- [Send emails from a different address or alias](https://support.google.com/mail/answer/22370?hl=en): "messages sent to the following aliases will all go to janedoe@gmail.com", e.g. `janedoe+school@gmail.com`.
- [Messages sent to a googlemail.com address](https://support.google.com/mail/answer/10313?hl=en): "Messages sent to @gmail.com and @googlemail.com are the same." and "Some countries use @googlemail.com instead, but messages sent to the address will go to the same place."
- [Dots don't matter in Gmail addresses](https://support.google.com/mail/answer/7436150?hl=en): `j.o.h.n.s.m.i.t.h@gmail.com` reaches `johnsmith@gmail.com`. This does not apply to Google Workspace accounts, see [custom domains](#custom-domains-google-workspace-exchange-online).

`googlemail.com` (documented by Google, see above; additionally checked with a delivery test on 2026-10-13):

- Especially relevant for German users, who were given `@googlemail.com` addresses until 2012.
- Delivery test: a mail sent to `<name>@googlemail.com` arrived in the inbox of `<name>@gmail.com`.
- So `<name>@googlemail.com` belongs exclusively to the owner of `<name>@gmail.com`: mail to it reaches that owner, so it can't be a separate person's mailbox.

### Outlook.com

| | |
|---|---|
| Name | `outlook` |
| Primary domain | `outlook.com` |
| Alias domains | none |
| Additional domains | none |
| Rules | `+tag` is removed |

Source:

- [Send email from a different address in Outlook.com](https://support.microsoft.com/en-us/outlook/send-email-from-a-different-address-in-outlook-com): mail to `yourname+site@outlook.com` arrives in your own inbox, and the tag stays visible.

The page only names `outlook.com`. See [other Microsoft domains](#hotmail-live-msn-and-outlook-country-domains).

### Proton Mail

| | |
|---|---|
| Name | `proton` |
| Primary domain | `proton.me` |
| Alias domains | none |
| Additional domains | `protonmail.com`, `protonmail.ch`, `pm.me`, each kept as it is |
| Rules | `+tag` is removed, then `.`, `_` and `-` are removed from the local part |

Source:

- [Email addresses and aliases](https://proton.me/support/addresses-and-aliases): "All mail sent to a plus alias arrives in your regular inbox. You don't need to set anything up." Plus aliases work for "any of your email addresses". The page lists all four domains.
- [Change your Proton username](https://proton.me/support/change-username) (checked 2026-10-17): dots, underscores and hyphens "are treated as transparent by our service. That is, username is the same as user_name and user.name", and "once a user takes a specific username, none of these variations can be used for a different account". So a variant can't belong to another person. The page speaks about usernames and doesn't mention delivery, so this rests on "the same as".

One account can have addresses on several Proton domains, but `name@proton.me` and `name@protonmail.com` are not guaranteed to be the same account, so the domains are not merged.

### Fastmail

| | |
|---|---|
| Name | `fastmail` |
| Primary domain | `fastmail.com` |
| Alias domains | none |
| Additional domains | 115 more domains from the official list, each kept as it is (e.g. `fastmail.fm`, `fastmail.de`, `sent.com`); the full list is in `FastmailDomainMatcher` |
| Rules | `+tag` is removed; subdomain addressing: `tag@user.fastmail.fm` becomes `user@fastmail.fm` |

Sources:

- [Plus addressing and subdomain addressing](https://www.fastmail.help/hc/en-us/articles/360060591053-Plus-addressing-and-subdomain-addressing): `alias+whateveryoulike@domain.tld` and `whateveryoulike@alias.domain.tld` both reach `alias@domain.tld`. For most users, "it will automatically work with any alias set up in your account". Only one subdomain level is supported, and the alias can't contain an underscore or a dot.
- [What domains can I use with Fastmail?](https://www.fastmail.help/hc/en-us/articles/6239365719055-What-domains-can-I-use-with-Fastmail): "You can create an email address at any of the domains listed below." 130 domains as of 2026-10-13: 118 global, 8 EU region and 4 US region domains.

The domains are not merged: the list does not say that `name@fastmail.fm` and `name@fastmail.com` are the same mailbox.

Not applied:

- 14 domains from the official list are left out: `aliencamel.com`, `foobox.com`, `foobox.net`, `immerbox.com`, `immermail.com`, `lifetimeaddress.com`, `mailzone.com`, `onepost.net`, `penguinmail.com`, `permanentmail.com`, `pobox.com`, `siemprebox.com`, `siempremail.com`, `veribox.net`. They look like domains of Pobox, a separate forwarding service that [was acquired by Fastmail in 2015](https://fastmail.com/blog/get-more-from-pobox-with-personal-domains). This is not confirmed, and no official source says how plus and subdomain addressing work for Pobox users there. So we prefer a possible duplicate.
- Custom domains hosted at Fastmail are out of scope, see [custom domains](#custom-domains-google-workspace-exchange-online).

### Yandex

| | |
|---|---|
| Name | `yandex` |
| Primary domain | `yandex.com` |
| Alias domains | `yandex.ru`, `yandex.by`, `yandex.kz`, `ya.ru` |
| Additional domains | none |
| Rules | `+tag` is removed |

Sources:

- [Registering a mailbox](https://yandex.com/support/mail/reg.html): "your email address will consist of your username, the @ sign, and the "yandex.com" domain name (or one of its domain aliases)". So one username belongs to one account on every Yandex domain.
- Same page: you can "add the "+" sign or the "+" sign and any word […] at the end of your username", e.g. `alice.the.girl+teaforum@yandex.com`. Mail to it is "filtered into the Inbox folder of your real mailbox".
- [Additional addresses](https://yandex.com/support/mail/web/preferences/about-sender/additional-addresses.html): addresses in the country domains `ru`, `by` and `kz` and in the domain `ya.ru` "are considered aliases of your primary email address. Aliases are required to maintain the uniqueness of your username." Which of them an account gets depends on the country in its Yandex ID. Because the username is unique, `name@yandex.kz` can't belong to anyone other than the owner of `name@yandex.com`, so rewriting is safe even when an account doesn't have that alias.

`yandex.com` is the primary domain because Yandex's international documentation uses it, even though `yandex.ru` is probably more common.

Not applied:

- `yandex.ua` is used by other libraries but is not on the official page.
- The same page says a login with dots like `alice.the.girl` also gets the alias `alice-the-girl`. It only documents this direction, not whether a login registered with hyphens has a dotted alias, so dots and hyphens are kept as they are.
- Phone number aliases (e.g. `15641234567@yandex.com`) can't be mapped to the login and are left as they are.

### Posteo

| | |
|---|---|
| Name | `posteo` |
| Primary domain | `posteo.de` |
| Alias domains | none |
| Additional domains | `posteo.com`, `posteo.net`, `posteo.us`, `posteo.ca`, `posteo.uk`, `posteo.ie`, `posteo.es`, `posteo.at`, `posteo.ch`, `posteo.nl`, `posteo.be`, `posteo.lu`, each kept as it is |
| Rules | `+filter` is removed |

Sources (checked 2026-10-15):

- [What are filter addresses and how do I use them?](https://posteo.de/en/help/filter-addresses): "You can use filter addresses with your email account address or an email alias." Valid examples include `youraddress+shopping123@posteo.com` and `ihreadresse+shopping+newsletter@posteo.de`, so several filter words in a row work. They need no setup, and "you will find the email in your inbox".
- [Which domains are available to use as a Posteo alias address?](https://posteo.de/en/help/which-domains-are-available-to-use-as-a-posteo-alias-address): "You can choose from the following domains for your Posteo address when registering" lists 13 domains. A second list of 49 domains is "available for email alias addresses", and "an alias is an additional email address that belongs to your email account".

Not applied:

- The domains that are only offered for aliases (e.g. `posteo.eu`, `posteo.co.uk`) are left out. An alias is a separate address, and the page does not say that the same name on two domains is the same mailbox.
- The domains are not merged, for the same reason.

### mailbox.org

| | |
|---|---|
| Name | `mailbox-org` |
| Primary domain | `mailbox.org` |
| Alias domains | none |
| Additional domains | none |
| Rules | `+extension` is removed |

Sources (checked 2026-10-15):

- [Using mail extensions](https://kb.mailbox.org/en/private/e-mail/mail-extensions/) (knowledge base): "add as many extensions as you like to the part of your email address that comes before the @ symbol (the local part of your username), separated by a plus sign", e.g. `me+test@mailbox.org` for the address `me@mailbox.org`. Extensions are "not aliases".
- [What is an email alias and how does it protect against spam?](https://mailbox.org/en/blog/email-alias-protection-against-spam/) (the provider's blog): "the mail server simply ignores everything after the plus sign during delivery".

Custom domains hosted at mailbox.org are out of scope, see [custom domains](#custom-domains-google-workspace-exchange-online).

### Lowercase

| | |
|---|---|
| Name | `lowercase` |
| Primary domain | every domain (catch-all) |
| Alias domains | none |
| Additional domains | none |
| Rules | the local part is lowercased |

This is a fallback, not a provider of a specific service, so it needs no official source: it only does what the core does for every address (trim, lowercase the domain) plus lowercasing the local part. RFC 5321 allows case-sensitive local parts, but no common mail system uses them. It matches every domain, so it has to be the last provider.

## Not supported

These come up often, but have no official source yet. They can be added once one is found.

### Hotmail, Live, MSN and Outlook country domains

`hotmail.*`, `live.*`, `msn.com` and domains like `outlook.de` belong to the same service, but the official documentation only gives `outlook.com` as an example. A user on Microsoft Q&A reports that plus mails to Hotmail sometimes did not arrive. Re-checked 2026-10-17: the [plus address page](https://support.microsoft.com/en-us/outlook/send-email-from-a-different-address-in-outlook-com) names only `outlook.com`; claims that it works for Hotmail come from third parties only.

### iCloud

`icloud.com`, `me.com` and `mac.com`: user tests on Apple Stack Exchange say `+tag` works, but Apple does not document it.

[About your @icloud.com, @me.com, and @mac.com email addresses](https://support.apple.com/en-us/118230) says that older accounts can use several of these domains. It does not say that `name@me.com` and `name@icloud.com` always share the same name or reach the same mailbox, so the domains are not merged.

### Yahoo

Sources contradict each other. Yahoo's [disposable address help](https://help.yahoo.com/kb/att/SLN35808.html) says these addresses "consist of 2 parts, a "base name" and a "keyword" (basename-keyword@yahoo.com)" and that the base name, once set, "can't be changed". The base name is therefore not the account's mailbox name. The hyphen rule some libraries (e.g. validator.js) apply would break real addresses like `max-muster@yahoo.de`, so no rule is applied.

### Mail.ru

Other libraries remove `+tag`, but no official Mail.ru documentation was found (searched in English and Russian, 2026-10-17; only community posts).

### Zoho

sunaoka/email-normalizer-php applies `+tag` for `zoho.com` and `zohomail.com`. We found no official Zoho help page on plus addressing (checked 2026-10-15 and 2026-10-17). The only statement is a community thread from a Zoho staff member, which says mail to `name+tag@domain` reaches `name@domain` for accounts hosted in Zoho Mail, and which may be outdated.

### GMX, WEB.DE, T-Online, AOL

No official statement found.

### Custom domains (Google Workspace, Exchange Online)

Out of scope. These providers host mail for any domain, so the domain alone doesn't show who hosts it, and the behavior depends on the organization's settings:

- [Google Workspace](https://support.google.com/mail/answer/7436150?hl=en): `+tag` is supported, but dots count.
- [Plus Addressing in Exchange Online](https://learn.microsoft.com/en-us/exchange/recipients-in-exchange-online/plus-addressing-in-exchange-online): on by default, but admins can turn it off. Exchange first looks for a mailbox with the full address including `+tag`, and only strips the tag if none exists.

At most, these addresses could be flagged, never normalized.

## Rules we deliberately don't apply

Other libraries apply these rules. They merge addresses of different people, so we don't. Examples from [Gabrola/email-normalizer](https://github.com/Gabrola/email-normalizer) (checked 2026-10-13):

| Rule | Why it is wrong |
|---|---|
| Remove dots for `google.com` | `google.com` is a Google Workspace domain, and for Workspace accounts [dots count](https://support.google.com/mail/answer/7436150?hl=en). |
| Remove dots for `live.com` | Not documented by Microsoft, and reported as wrong in [issue #2](https://github.com/Gabrola/email-normalizer/issues/2) and [PR #5](https://github.com/Gabrola/email-normalizer/pull/5). |
| Cut off everything after `-` for Yahoo | Breaks real addresses like `max-muster@yahoo.de`. The `basename-keyword` form only exists for disposable addresses, whose base name is chosen separately, see [Yahoo](#yahoo). |
| Rewrite `me.com` and `mac.com` to `icloud.com` | Not documented by Apple, see [iCloud](#icloud). |
| Rewrite `yandex.ua` to `yandex.com` | Not on Yandex's list of additional addresses, see [Yandex](#yandex). |

Rules from [sunaoka/email-normalizer-php](https://github.com/sunaoka/email-normalizer-php) and [rezozero/canonical-email](https://github.com/rezozero/canonical-email) (checked 2026-10-15):

| Rule | Why it is wrong |
|---|---|
| Fold `me.com` and `mac.com` into `icloud.com` (sunaoka) | Not documented by Apple, see [iCloud](#icloud). |
| Remove `+tag` for `hotmail.com`, `live.com` and `msn.com` (sunaoka) | Not documented by Microsoft, see [Hotmail, Live, MSN](#hotmail-live-msn-and-outlook-country-domains). |
| Remove `+tag` for any domain whose MX records point at a known provider (sunaoka, rezozero) | The domain alone doesn't show who administers the mailbox: Google Workspace and Exchange Online admins configure plus addressing and dots themselves, see [custom domains](#custom-domains-google-workspace-exchange-online). A DNS lookup also makes the stored key depend on the network and the current DNS state. |
