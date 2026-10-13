<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use TheDomeFfm\EmailCanonicalizer\Exception\InvalidEmailException;

interface EmailCanonicalizerInterface
{
    /**
     * Maps variants of the same mailbox to the same canonical email.
     * Never use the result for login or for sending mail.
     *
     * @throws InvalidEmailException
     */
    public function canonicalize(string $email): CanonicalEmail;
}
