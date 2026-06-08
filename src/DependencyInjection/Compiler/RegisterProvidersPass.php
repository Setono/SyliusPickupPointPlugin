<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler;

use InvalidArgumentException;
use ReflectionClass;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ProviderRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(ProviderRegistry::class);

        /** @var array<string, string> $codeToNameMap */
        $codeToNameMap = [];
        foreach ($container->findTaggedServiceIds('setono_sylius_pickup_point.provider') as $id => $tagged) {
            foreach ($tagged as $attributes) {
                [$code, $name] = $this->resolveCodeAndName($container, $id, is_array($attributes) ? $attributes : []);

                if (isset($codeToNameMap[$code])) {
                    throw new NonUniqueProviderCodeException($code);
                }

                $codeToNameMap[$code] = $name;

                $definition = $container->getDefinition($id);

                // Resolve the code into the provider instance once, at compile time, so it can
                // stamp the code onto the pickup points it returns without runtime reflection.
                $definition->addMethodCall('setCode', [$code]);

                // Providers are lazy so that one whose construction reaches out to an external service
                // — e.g. the GLS provider opening a SOAP client against a possibly-down WSDL — is built
                // only when actually called (inside PickupPointsAction's per-provider try/catch), rather
                // than failing the resolution of the whole registry, the checkout form, or the endpoint.
                //
                // The concrete providers are `final`, so the default lazy *ghost* — which works by
                // subclassing the class — cannot be generated on PHP < 8.4 (which uses VarExporter-generated
                // proxies rather than native lazy objects). "Interface proxifying" sidesteps that: the
                // `proxy` tag makes Symfony build a virtual proxy that *implements* ProviderInterface instead
                // of extending the final class, which works on every supported PHP version. Every method
                // called on the proxy (setCode/findPickupPoints/findPickupPoint) is declared on the interface.
                $definition->setLazy(true);
                $definition->addTag('proxy', ['interface' => ProviderInterface::class]);

                $registry->addMethodCall('add', [new Reference($id), $code, $name]);
            }
        }

        $container->setParameter('setono_sylius_pickup_point.providers', $codeToNameMap);
    }

    /**
     * @param array<int|string, mixed> $tagAttributes
     *
     * @return array{0: string, 1: string}
     */
    private function resolveCodeAndName(ContainerBuilder $container, string $id, array $tagAttributes): array
    {
        $code = $tagAttributes['code'] ?? null;
        $name = $tagAttributes['name'] ?? null;

        if (is_string($code) && '' !== $code && is_string($name) && '' !== $name) {
            return [$code, $name];
        }

        $class = $container->getDefinition($id)->getClass();
        if (is_string($class) && class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(AsProvider::class);
            if ([] !== $attributes) {
                $attribute = $attributes[0]->newInstance();

                return [$attribute->code, $attribute->name];
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Tagged pickup point provider "%s" must either set `code` and `name` tag attributes or declare the #[%s] attribute on its class.',
            $id,
            AsProvider::class,
        ));
    }
}
