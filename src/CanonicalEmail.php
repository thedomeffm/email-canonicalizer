<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use Stringable;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

/**
 * Never use the canonical email for login or for sending mail.
 */
final readonly class CanonicalEmail implements Stringable
{
    public function __construct(
        public string $original,
        public string $localPart,
        public string $domain,
        public ?ProviderInterface $provider,
    ) {}

    public function __toString(): string
    {
        return $this->email();
    }

    public function email(): string
    {
        return $this->localPart . '@' . $this->domain;
    }
}
