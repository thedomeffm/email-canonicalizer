<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use ArrayIterator;
use IteratorAggregate;
use TheDomeFfm\EmailCanonicalizer\Provider\FastmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\MailboxOrgProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\OutlookProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\PosteoProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProtonProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;
use TheDomeFfm\EmailCanonicalizer\Provider\YandexProvider;

/**
 * All built-in providers for public email services, without the lowercase fallback.
 * Spread it to extend the list: `[...new PublicMailProviders(), new MyProvider(), new LowercaseProvider()]`.
 *
 * @implements IteratorAggregate<int, ProviderInterface>
 */
final readonly class PublicMailProviders implements IteratorAggregate
{
    /**
     * @return ArrayIterator<int, ProviderInterface>
     */
    public function getIterator(): ArrayIterator
    {
        /** @var list<ProviderInterface> $providers */
        $providers = [
            new GmailProvider(),
            new OutlookProvider(),
            new ProtonProvider(),
            new FastmailProvider(),
            new YandexProvider(),
            new PosteoProvider(),
            new MailboxOrgProvider(),
        ];

        return new ArrayIterator($providers);
    }
}
