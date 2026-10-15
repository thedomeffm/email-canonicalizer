<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\EmailAddress;

/**
 * Rewrites alias domains to one target domain, e.g. "googlemail.com" to "gmail.com".
 * Only use it for domains that provably reach the same mailbox.
 */
final readonly class RewriteDomainRule implements RuleInterface
{
    /**
     * @param list<string> $from
     */
    public function __construct(
        private string $to,
        private array $from,
    ) {
        if ($to === '' || $from === [] || in_array('', $from, true)) {
            throw new InvalidArgumentException('A target domain and at least one non-empty domain to rewrite are required.');
        }
    }

    public function apply(EmailAddress $emailAddress): EmailAddress
    {
        if (! in_array($emailAddress->domain, $this->from, true)) {
            return $emailAddress;
        }

        return new EmailAddress($emailAddress->localPart, $this->to);
    }
}
