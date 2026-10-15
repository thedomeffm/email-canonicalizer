<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\RemoveCharactersRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;

/**
 * Test double: removes dots from the local part for the configured domain.
 */
final readonly class StripDotsProvider implements ProviderInterface
{
    public function __construct(
        private string $name = 'strip-dots',
        private string $domain = 'example.com',
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher($this->domain);
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new RemoveCharactersRule('.')];
    }
}
