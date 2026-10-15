<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\EmailAddress;

/**
 * Removes characters from the local part, e.g. ignoring dots like Gmail does.
 */
final readonly class RemoveCharactersRule implements RuleInterface
{
    /**
     * @var list<string>
     */
    private array $characters;

    public function __construct(string ...$characters)
    {
        if ($characters === []) {
            throw new InvalidArgumentException('At least one character to remove is required.');
        }

        if (in_array('', $characters, true)) {
            throw new InvalidArgumentException('Characters to remove must not be empty.');
        }

        $this->characters = array_values($characters);
    }

    public function apply(EmailAddress $emailAddress): EmailAddress
    {
        return new EmailAddress(str_replace($this->characters, '', $emailAddress->localPart), $emailAddress->domain);
    }
}
