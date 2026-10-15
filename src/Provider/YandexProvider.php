<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Matcher\DomainMatcherInterface;
use TheDomeFfm\EmailCanonicalizer\Matcher\ExactDomainMatcher;
use TheDomeFfm\EmailCanonicalizer\Rule\RewriteDomainRule;
use TheDomeFfm\EmailCanonicalizer\Rule\RuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Yandex Mail: removes "+tag". Dots and hyphens in the local part count.
 * yandex.ru, yandex.by, yandex.kz and ya.ru are documented aliases and are rewritten
 * to yandex.com, the domain Yandex's international documentation uses.
 *
 * @see docs/providers.md#yandex
 */
final readonly class YandexProvider implements ProviderInterface
{
    public function name(): string
    {
        return 'yandex';
    }

    public function domainMatcher(): DomainMatcherInterface
    {
        return new ExactDomainMatcher('yandex.com', 'yandex.ru', 'yandex.by', 'yandex.kz', 'ya.ru');
    }

    /**
     * @return list<RuleInterface>
     */
    public function rules(): array
    {
        return [new SubaddressRule('+'), new RewriteDomainRule('yandex.com', ['yandex.ru', 'yandex.by', 'yandex.kz', 'ya.ru'])];
    }
}
