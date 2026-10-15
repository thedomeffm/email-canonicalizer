<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use TheDomeFfm\EmailCanonicalizer\EmailAddress;

interface RuleInterface
{
    /**
     * @param EmailAddress $emailAddress the address with a lowercased local part and domain
     */
    public function apply(EmailAddress $emailAddress): EmailAddress;
}
