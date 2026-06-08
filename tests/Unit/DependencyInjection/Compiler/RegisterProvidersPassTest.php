<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler\RegisterProvidersPass;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use Setono\SyliusPickupPointPlugin\Provider\FakerProvider;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProvidersPassTest extends TestCase
{
    private ContainerBuilder $container;

    private RegisterProvidersPass $pass;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setDefinition(ProviderRegistry::class, new Definition(ProviderRegistry::class));

        $this->pass = new RegisterProvidersPass();
    }

    public function testItDoesNothingWhenTheRegistryIsNotDefined(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));

        $this->pass->process($container);

        self::assertSame([], $container->getDefinition('provider.gls')->getMethodCalls());
        self::assertFalse($container->hasParameter('setono_sylius_pickup_point.providers'));
    }

    public function testItStampsTheCodeOntoTheProviderDefinition(): void
    {
        $this->container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));

        $this->pass->process($this->container);

        $calls = $this->container->getDefinition('provider.gls')->getMethodCalls();

        self::assertContains(['setCode', ['gls']], $calls);
    }

    public function testItMakesTheProviderLazyAndProxiesItThroughTheInterface(): void
    {
        $this->container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition('provider.gls');

        self::assertTrue($definition->isLazy());
        self::assertSame(
            [['interface' => ProviderInterface::class]],
            $definition->getTag('proxy'),
        );
    }

    public function testItRegistersTheProviderIntoTheRegistry(): void
    {
        $this->container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));

        $this->pass->process($this->container);

        $calls = $this->container->getDefinition(ProviderRegistry::class)->getMethodCalls();

        self::assertCount(1, $calls);
        $call = $calls[0];
        self::assertIsArray($call);
        [$method, $arguments] = $call;
        self::assertSame('add', $method);
        self::assertIsArray($arguments);
        self::assertInstanceOf(Reference::class, $arguments[0]);
        self::assertSame('provider.gls', (string) $arguments[0]);
        self::assertSame('gls', $arguments[1]);
        self::assertSame('GLS', $arguments[2]);
    }

    public function testItExposesTheCodeToNameMapAsAParameter(): void
    {
        $this->container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));
        $this->container->setDefinition('provider.dao', $this->createProviderDefinition('dao', 'DAO'));

        $this->pass->process($this->container);

        self::assertSame(
            ['gls' => 'GLS', 'dao' => 'DAO'],
            $this->container->getParameter('setono_sylius_pickup_point.providers'),
        );
    }

    public function testItResolvesTheCodeAndNameFromTheAsProviderAttributeWhenTagAttributesAreMissing(): void
    {
        $definition = new Definition(FakerProvider::class);
        $definition->addTag('setono_sylius_pickup_point.provider');
        $this->container->setDefinition('provider.faker', $definition);

        $this->pass->process($this->container);

        self::assertSame(
            ['faker' => 'Faker'],
            $this->container->getParameter('setono_sylius_pickup_point.providers'),
        );
        self::assertContains(['setCode', ['faker']], $this->container->getDefinition('provider.faker')->getMethodCalls());
    }

    public function testItThrowsWhenTwoProvidersShareTheSameCode(): void
    {
        $this->container->setDefinition('provider.gls', $this->createProviderDefinition('gls', 'GLS'));
        $this->container->setDefinition('provider.gls_clone', $this->createProviderDefinition('gls', 'GLS clone'));

        $this->expectException(NonUniqueProviderCodeException::class);

        $this->pass->process($this->container);
    }

    public function testItThrowsWhenNeitherTagAttributesNorAttributeProvideACode(): void
    {
        $definition = new Definition(\stdClass::class);
        $definition->addTag('setono_sylius_pickup_point.provider');
        $this->container->setDefinition('provider.broken', $definition);

        $this->expectException(\InvalidArgumentException::class);

        $this->pass->process($this->container);
    }

    private function createProviderDefinition(string $code, string $name): Definition
    {
        $definition = new Definition(\stdClass::class);
        $definition->addTag('setono_sylius_pickup_point.provider', ['code' => $code, 'name' => $name]);

        return $definition;
    }
}
