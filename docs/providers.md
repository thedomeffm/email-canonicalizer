# Providers

This document is the evidence behind every provider in this library. Each provider class in `src/Provider/` links here, and every domain, alias and rule a provider applies must be listed below with its source.

Last checked: 2026-10-13.

## Rules for adding a provider

- **Only official documentation counts.** A help page or documentation from the provider itself. Blog posts, Stack Exchange answers, user reports and other libraries are not enough.
- **One exception: a verified delivery test.** If no official source exists, a real delivery test by a maintainer can back an alias domain. It must be marked as such below, with the date of the test. So far this applies only to `googlemail.com`.
- **Domains are not merged unless documented.** Similar-looking domains of one provider can be separate mailboxes. A domain only becomes an alias domain if the provider documents that it reaches the same mailbox as the primary domain. Otherwise it is an additional domain.
- **When in doubt, leave it out.** A missing rule only lets a duplicate account slip through. A wrong rule merges different people and blocks a real user.

## Supported providers

The canonicalizer always trims and lowercases the whole address first. The rules below are applied on top of that.

Each provider has three kinds of domains:

- **Primary domain:** the provider's most common domain.
- **Alias domains:** documented (or verified by a delivery test) to reach the same mailbox as the primary domain. They are rewritten to the primary domain.
- **Additional domains:** the provider's rules apply, but they are separate mailboxes. They are kept as they are.

### Gmail

| | |
|---|---|
| Name | `gmail` |
| Primary domain | `gmail.com` |
| Alias domains | `googlemail.com`, verified by test, see below |
| Additional domains | none |
| Rules | `+tag` is removed, dots in the local part are removed |

Sources:

- [Send emails from a different address or alias](https://support.google.com/mail/answer/22370?hl=en): "messages sent to the following aliases will all go to janedoe@gmail.com", e.g. `janedoe+school@gmail.com`.
- [Dots don't matter in Gmail addresses](https://support.google.com/mail/answer/7436150?hl=en): `j.o.h.n.s.m.i.t.h@gmail.com` reaches `johnsmith@gmail.com`. This does not apply to Google Workspace accounts, see [custom domains](#custom-domains-google-workspace-exchange-online).

`googlemail.com` (verified by test on 2026-10-13, not officially documented):

- Widely known as an alias domain of `gmail.com`, and especially relevant for German users, who were given `@googlemail.com` addresses until 2012. No current official Google page confirms it.
- Delivery test: a mail sent to `<name>@googlemail.com` arrived in the inbox of `<name>@gmail.com`.
- So `<name>@googlemail.com` belongs exclusively to the owner of `<name>@gmail.com`: mail to it reaches that owner, so it can't be a separate person's mailbox.
- Context, not official: a [Gmail Community thread](https://support.google.com/mail/thread/378507106/confused-email-addresses?hl=en) and a [Google Groups discussion](https://groups.google.com/g/gmail-users/c/wH0itYAIodQ) describe the two domains as one mailbox.

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
| Rules | `+tag` is removed |

Source:

- [Email addresses and aliases](https://proton.me/support/addresses-and-aliases): "All mail sent to a plus alias arrives in your regular inbox. You don't need to set anything up." Plus aliases work for "any of your email addresses". The page lists all four domains.

One account can have addresses on several Proton domains, but `name@proton.me` and `name@protonmail.com` are not guaranteed to be the same account, so the domains are not merged.

### Fastmail

| | |
|---|---|
| Name | `fastmail` |
| Primary domain | `fastmail.com` |
| Alias domains | none |
| Additional domains | none |
| Rules | `+tag` is removed; subdomain addressing: `tag@user.fastmail.com` becomes `user@fastmail.com` |

Source:

- [Plus addressing and subdomain addressing](https://www.fastmail.help/hc/en-us/articles/360060591053-Plus-addressing-and-subdomain-addressing): `alias+whateveryoulike@domain.tld` and `whateveryoulike@alias.domain.tld` both reach `alias@domain.tld`. Only one subdomain level is supported, and the alias can't contain an underscore or a dot.

Fastmail has more domains (e.g. `fastmail.fm`), but no official list was found, so only `fastmail.com` is supported.

## Not supported

These come up often, but have no official source yet. They can be added once one is found.

### Hotmail, Live, MSN and Outlook country domains

`hotmail.*`, `live.*`, `msn.com` and domains like `outlook.de` belong to the same service, but the official documentation only gives `outlook.com` as an example. A user on Microsoft Q&A reports that plus mails to Hotmail sometimes did not arrive.

### iCloud

`icloud.com`, `me.com` and `mac.com`: user tests on Apple Stack Exchange say `+tag` works, but Apple does not document it.

### Yahoo

Sources contradict each other, and the Yahoo help page was not reachable. The hyphen rule some libraries (e.g. validator.js) apply would break real addresses like `max-muster@yahoo.de`, so no rule is applied.

### Yandex

Rules only appear in third-party libraries, not in Yandex's own documentation.

### GMX, WEB.DE, T-Online, AOL

No official statement found.

### Custom domains (Google Workspace, Exchange Online)

Out of scope. These providers host mail for any domain, so the domain alone doesn't show who hosts it, and the behavior depends on the organization's settings:

- [Google Workspace](https://support.google.com/mail/answer/7436150?hl=en): `+tag` is supported, but dots count.
- [Plus Addressing in Exchange Online](https://learn.microsoft.com/en-us/exchange/recipients-in-exchange-online/plus-addressing-in-exchange-online): on by default, but admins can turn it off. Exchange first looks for a mailbox with the full address including `+tag`, and only strips the tag if none exists.

At most, these addresses could be flagged, never normalized.
