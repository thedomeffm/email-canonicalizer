<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\EmailAddress;

/**
 * Removes everything from the first separator onward, e.g. "john+newsletter" becomes "john".
 */
final readonly class SubaddressRule implements RuleInterface
{
    public function __construct(
        private string $separator = '+',
    ) {
        if ($separator === '') {
            throw new InvalidArgumentException('The subaddress separator must not be empty.');
        }
    }

    public function apply(EmailAddress $emailAddress): EmailAddress
    {
        $separatorPosition = strpos($emailAddress->localPart, $this->separator);

        // A local part that starts with the separator has nothing left to keep.
        if ($separatorPosition === false || $separatorPosition === 0) {
            return $emailAddress;
        }

        return new EmailAddress(substr($emailAddress->localPart, 0, $separatorPosition), $emailAddress->domain);
    }
}
