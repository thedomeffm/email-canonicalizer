<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;

/**
 * Removes everything from the first separator onward, e.g. "john+newsletter" becomes "john".
 */
final readonly class SubaddressRule implements LocalPartRuleInterface
{
    public function __construct(
        private string $separator = '+',
    ) {
        if ($separator === '') {
            throw new InvalidArgumentException('The subaddress separator must not be empty.');
        }
    }

    public function apply(string $localPart): string
    {
        $separatorPosition = strpos($localPart, $this->separator);

        // A local part that starts with the separator has nothing left to keep.
        if ($separatorPosition === false || $separatorPosition === 0) {
            return $localPart;
        }

        return substr($localPart, 0, $separatorPosition);
    }
}
