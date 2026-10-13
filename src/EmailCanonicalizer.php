<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use TheDomeFfm\EmailCanonicalizer\Provider\FastmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\GmailProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\OutlookProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProtonProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

final readonly class EmailCanonicalizer implements EmailCanonicalizerInterface
{
    /**
     * @var list<ProviderInterface>
     */
    private array $providers;

    /**
     * Without arguments, all built-in providers are used. Passed providers replace them;
     * combine both with `new EmailCanonicalizer(...EmailCanonicalizer::defaultProviders(), $myProvider)`.
     */
    public function __construct(ProviderInterface ...$providers)
    {
        $this->providers = $providers === [] ? self::defaultProviders() : array_values($providers);
    }

    /**
     * @return list<ProviderInterface>
     */
    public static function defaultProviders(): array
    {
        return [new GmailProvider(), new OutlookProvider(), new ProtonProvider(), new FastmailProvider()];
    }

    public function canonicalize(string $email): CanonicalEmail
    {
        $emailAddress = EmailAddress::fromString($email);
        $provider = $this->findProvider($emailAddress->domain);

        if ($provider instanceof ProviderInterface) {
            $emailAddress = $provider->applyRules($emailAddress);
        }

        return new CanonicalEmail($email, $emailAddress->localPart, $emailAddress->domain, $provider);
    }

    private function findProvider(string $domain): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($domain)) {
                return $provider;
            }
        }

        return null;
    }
}
