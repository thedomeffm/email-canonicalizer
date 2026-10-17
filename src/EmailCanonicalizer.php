<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\Matcher\AnyDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\LowercaseProvider;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

final readonly class EmailCanonicalizer implements EmailCanonicalizerInterface
{
    /**
     * @var list<ProviderInterface>
     */
    private array $providers;

    /**
     * The first provider whose domain matcher matches wins. Use `EmailCanonicalizer::default()` for the usual setup.
     *
     * @throws InvalidArgumentException if there is no provider, or if a provider that matches
     *                                  every domain (like `LowercaseProvider`) is not the last one
     */
    public function __construct(ProviderInterface ...$providers)
    {
        if ($providers === []) {
            throw new InvalidArgumentException('At least one provider is required. Use EmailCanonicalizer::default(), or LowercaseProvider to only trim and lowercase.');
        }

        foreach (array_slice($providers, 0, -1) as $provider) {
            if ($provider->domainMatcher() instanceof AnyDomainMatcher) {
                throw new InvalidArgumentException(sprintf('Provider "%s" matches every domain, so it must be the last provider.', $provider->name()));
            }
        }

        $this->providers = array_values($providers);
    }

    /**
     * Your own providers first, then all public mail providers, then `LowercaseProvider` for every other domain.
     */
    public static function default(ProviderInterface ...$customProviders): self
    {
        return new self(...[...$customProviders, ...new PublicMailProviders(), new LowercaseProvider()]);
    }

    /**
     * Only trims the address and lowercases it, with no provider rules.
     */
    public static function lowercaseOnly(): self
    {
        return new self(new LowercaseProvider());
    }

    public function canonicalize(string $email): CanonicalEmail
    {
        $emailAddress = EmailAddress::fromString($email);
        $provider = $this->findProvider($emailAddress->domain);

        if ($provider instanceof ProviderInterface) {
            $emailAddress = $this->applyRules($provider, $emailAddress);
        }

        return new CanonicalEmail($email, $emailAddress->localPart, $emailAddress->domain, $provider);
    }

    private function findProvider(string $domain): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->domainMatcher()->matches($domain)) {
                return $provider;
            }
        }

        return null;
    }

    private function applyRules(ProviderInterface $provider, EmailAddress $emailAddress): EmailAddress
    {
        $emailAddress = new EmailAddress(mb_strtolower($emailAddress->localPart), $emailAddress->domain);

        foreach ($provider->rules() as $rule) {
            $result = $rule->apply($emailAddress);

            // An empty local part would produce a broken address, so the rule is skipped.
            if ($result->localPart !== '') {
                $emailAddress = $result;
            }
        }

        return $emailAddress;
    }
}
