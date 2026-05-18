<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use LogicException;
use ReflectionClass;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;

abstract class Provider implements ProviderInterface
{
    /** @var array<class-string, AsProvider> */
    private static array $attributeCache = [];

    public function __toString(): string
    {
        return $this->getCode();
    }

    public function getCode(): string
    {
        return self::resolveAsProvider(static::class)->code;
    }

    public function getName(): string
    {
        return self::resolveAsProvider(static::class)->name;
    }

    /**
     * @param class-string $class
     */
    private static function resolveAsProvider(string $class): AsProvider
    {
        if (!isset(self::$attributeCache[$class])) {
            $attributes = (new ReflectionClass($class))->getAttributes(AsProvider::class);
            if ([] === $attributes) {
                throw new LogicException(sprintf(
                    'Provider "%s" must declare the #[%s] attribute or override getCode() / getName().',
                    $class,
                    AsProvider::class,
                ));
            }

            self::$attributeCache[$class] = $attributes[0]->newInstance();
        }

        return self::$attributeCache[$class];
    }
}
