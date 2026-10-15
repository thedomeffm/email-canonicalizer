<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use TheDomeFfm\EmailCanonicalizer\EmailCanonicalizer;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

final class Canonical
{
    /**
     * The canonical address when only the given provider is active.
     */
    public static function email(ProviderInterface $provider, string $email): string
    {
        return (new EmailCanonicalizer($provider))->canonicalize($email)->email();
    }
}
