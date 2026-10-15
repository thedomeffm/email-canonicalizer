<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Rule;

use InvalidArgumentException;

/**
 * Replaces characters first, then removes characters, e.g. ignoring dots like Gmail does.
 */
final readonly class CharacterRule implements LocalPartRuleInterface
{
    /**
     * @param list<string> $remove
     * @param array<string, string> $replace
     */
    public function __construct(
        private array $remove = [],
        private array $replace = [],
    ) {
        if (in_array('', $remove, true)) {
            throw new InvalidArgumentException('Characters to remove must not be empty.');
        }

        foreach (array_keys($replace) as $search) {
            if ((string) $search === '') {
                throw new InvalidArgumentException('Characters to replace must not be empty.');
            }
        }
    }

    public function apply(string $localPart): string
    {
        return str_replace($this->remove, '', strtr($localPart, $this->replace));
    }
}
