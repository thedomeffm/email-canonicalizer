<?php

declare(strict_types=1);

namespace TheDomeFfm\EmailCanonicalizer\Provider;

use TheDomeFfm\EmailCanonicalizer\Rule\LocalPartRuleInterface;
use TheDomeFfm\EmailCanonicalizer\Rule\SubaddressRule;

/**
 * Yandex Mail: removes "+tag". Dots and hyphens in the local part count.
 * yandex.ru, yandex.by, yandex.kz and ya.ru are documented aliases and are rewritten
 * to yandex.com, the domain Yandex's international documentation uses.
 *
 * @see docs/providers.md#yandex
 */
final class YandexProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'yandex';
    }

    protected function primaryDomain(): string
    {
        return 'yandex.com';
    }

    protected function aliasDomains(): array
    {
        return ['yandex.ru', 'yandex.by', 'yandex.kz', 'ya.ru'];
    }

    /**
     * @return list<LocalPartRuleInterface>
     */
    protected function rules(): array
    {
        return [new SubaddressRule('+')];
    }
}
