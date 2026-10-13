<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Exception;

use InvalidArgumentException;

final class InvalidEmailException extends InvalidArgumentException
{
    public static function fromEmail(string $email): self
    {
        return new self(sprintf('"%s" is not a valid email address.', $email));
    }
}
