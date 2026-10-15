<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use InvalidArgumentException;
use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;

/**
 * A provider you can build in one line, e.g. for your own business:
 * `new GenericProvider('our-business', new ExactDomainMatcher('example.com'), new SubaddressRule('-'))`.
 */
final readonly class GenericProvider implements ProviderInterface
{
    /**
     * @var list<RuleInterface>
     */
    private array $rules;

    public function __construct(
        private string $name,
        private DomainMatcherInterface $domainMatcher,
        RuleInterface ...$rules,
    ) {
        if ($name === '') {
            throw new InvalidArgumentException('The provider needs a name.');
        }

        $this->rules = array_values($rules);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return $this->domainMatcher;
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return $this->rules;
    }
}
