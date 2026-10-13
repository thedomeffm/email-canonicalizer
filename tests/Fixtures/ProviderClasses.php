<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use ReflectionClass;
use TheDomeFfm\EmailCanonicalizer\Provider\ProviderInterface;

final class ProviderClasses
{
    /**
     * Every instantiable provider class in src/Provider/.
     *
     * @return list<ReflectionClass<ProviderInterface>>
     */
    public static function concrete(): array
    {
        $providers = [];

        foreach (glob(dirname(__DIR__, 2) . '/src/Provider/*.php') ?: [] as $file) {
            $className = 'TheDomeFfm\\EmailCanonicalizer\\Provider\\' . basename($file, '.php');
            $reflectionClass = new ReflectionClass($className);

            if ($reflectionClass->isInstantiable() && $reflectionClass->implementsInterface(ProviderInterface::class)) {
                $providers[] = $reflectionClass;
            }
        }

        return $providers;
    }
}
