<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;
use TheDomeFfm\EmailCanonicalizer\Exception\MissingExtensionException;

/**
 * A parsed address. The domain is trimmed, lowercased and in punycode; the local part keeps its case.
 */
final readonly class EmailAddress
{
    public function __construct(
        public string $localPart,
        public string $domain,
    ) {}

    /**
     * Trims the address, rejects quoted local parts, and lowercases the domain (internationalized
     * domains are converted to punycode). The local part keeps its case: providers lowercase it.
     *
     * @param bool|null $idnSupported whether idn_to_ascii() is available, null to detect it
     *
     * @throws InvalidEmailException
     * @throws MissingExtensionException when the domain is internationalized and ext-intl is missing
     */
    public static function fromString(string $email, ?bool $idnSupported = null): self
    {
        $trimmed = trim($email);
        $atPosition = strrpos($trimmed, '@');

        if ($atPosition === false) {
            throw InvalidEmailException::fromEmail($email);
        }

        $localPart = substr($trimmed, 0, $atPosition);
        $domain = substr($trimmed, $atPosition + 1);

        if (str_starts_with($localPart, '"')) {
            throw InvalidEmailException::fromEmail($email);
        }

        if (preg_match('/[^\x00-\x7F]/', $domain) === 1) {
            if (! ($idnSupported ?? function_exists('idn_to_ascii'))) {
                throw MissingExtensionException::forInternationalizedDomain($domain);
            }

            $domain = idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);

            if ($domain === false) {
                throw InvalidEmailException::fromEmail($email);
            }
        }

        $domain = strtolower($domain);

        if (filter_var($localPart . '@' . $domain, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false) {
            throw InvalidEmailException::fromEmail($email);
        }

        return new self($localPart, $domain);
    }
}
