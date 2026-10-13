<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use TheDomeFfm\EmailCanonicalizer\Provider\AbstractProvider;
use TheDomeFfm\EmailCanonicalizer\Rule\CharacterRule;
use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Test double: a primary domain, an additional domain, two aliases, and the subaddress and dot rules.
 */
final class ExampleProvider extends AbstractProvider
{
    /**
     * @var list<LocalPartRuleInterface>
     */
    private readonly array $rules;

    /**
     * @param list<LocalPartRuleInterface>|null $rules
     */
    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? [new SubaddressRule(), new CharacterRule(remove: ['.'])];
    }

    public function name(): string
    {
        return 'example';
    }

    protected function primaryDomain(): string
    {
        return 'example.com';
    }

    protected function additionalDomains(): array
    {
        return ['example.org'];
    }

    protected function aliasDomains(): array
    {
        return ['example.net', 'example.info'];
    }

    /**
     * @return list<LocalPartRuleInterface>
     */
    protected function rules(): array
    {
        return $this->rules;
    }
}
