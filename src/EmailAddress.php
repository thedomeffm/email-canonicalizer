<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;

/**
 * @internal
 */
final readonly class EmailAddress
{
    public function __construct(
        public string $localPart,
        public string $domain,
    ) {}

    /**
     * Trims and lowercases the whole address. RFC 5321 allows case-sensitive local parts,
     * but no common mail system uses them, and lowercasing catches more duplicates.
     *
     * @throws InvalidEmailException
     */
    public static function fromString(string $email): self
    {
        $normalized = strtolower(trim($email));

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false) {
            throw InvalidEmailException::fromEmail($email);
        }

        $atPosition = (int) strrpos($normalized, '@');

        return new self(substr($normalized, 0, $atPosition), substr($normalized, $atPosition + 1));
    }
}
