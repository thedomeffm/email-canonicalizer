<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Exception;

use LogicException;

/**
 * The address is valid, but the server lacks a PHP extension needed to canonicalize it reliably.
 */
final class MissingExtensionException extends LogicException
{
    public static function forInternationalizedDomain(string $domain): self
    {
        return new self(sprintf(
            'The domain "%s" is internationalized. Install ext-intl (or symfony/polyfill-intl-idn) to canonicalize it.',
            $domain,
        ));
    }
}
