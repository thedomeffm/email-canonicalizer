<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

interface LocalPartRuleInterface
{
    /**
     * @param string $localPart trimmed and lowercased local part
     */
    public function apply(string $localPart): string;
}
