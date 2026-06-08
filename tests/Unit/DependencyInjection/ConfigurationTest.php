<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Setono\DAOBundle\SetonoDAOBundle;
use Setono\GlsWebserviceBundle\SetonoGlsWebserviceBundle;
use Setono\PostNordBundle\SetonoPostNordBundle;
use Setono\SyliusPickupPointPlugin\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /**
     * Processes the configuration and returns the resolved `providers` toggle map.
     *
     * @param array<array-key, mixed> $configs
     *
     * @return array<array-key, mixed>
     */
    private function processProviders(array $configs): array
    {
        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        self::assertIsArray($config['providers']);

        return $config['providers'];
    }

    public function testItDisablesTheFakerProviderByDefault(): void
    {
        $providers = $this->processProviders([]);

        self::assertFalse($providers['faker']);
    }

    public function testTheThirdPartyProviderDefaultsFollowBundleAvailability(): void
    {
        $providers = $this->processProviders([]);

        // The DAO/GLS/PostNord providers default to enabled only when their
        // optional bundle is installed, so the expected default is computed the
        // same way the Configuration does instead of being hardcoded.
        self::assertSame(class_exists(SetonoDAOBundle::class), $providers['dao']);
        self::assertSame(class_exists(SetonoGlsWebserviceBundle::class), $providers['gls']);
        self::assertSame(class_exists(SetonoPostNordBundle::class), $providers['post_nord']);
    }

    public function testSuppliedValuesOverrideTheDefaults(): void
    {
        $providers = $this->processProviders([
            'setono_sylius_pickup_point' => [
                'providers' => [
                    'faker' => true,
                    'dao' => false,
                    'gls' => false,
                    'post_nord' => false,
                ],
            ],
        ]);

        self::assertTrue($providers['faker']);
        self::assertFalse($providers['dao']);
        self::assertFalse($providers['gls']);
        self::assertFalse($providers['post_nord']);
    }

    public function testEnablingEveryProviderExplicitly(): void
    {
        $providers = $this->processProviders([
            'setono_sylius_pickup_point' => [
                'providers' => [
                    'faker' => true,
                    'dao' => true,
                    'gls' => true,
                    'post_nord' => true,
                ],
            ],
        ]);

        self::assertTrue($providers['faker']);
        self::assertTrue($providers['dao']);
        self::assertTrue($providers['gls']);
        self::assertTrue($providers['post_nord']);
    }

    public function testASuppliedProviderTogglesLeaveTheOthersAtTheirDefault(): void
    {
        $providers = $this->processProviders([
            'setono_sylius_pickup_point' => [
                'providers' => [
                    'faker' => true,
                ],
            ],
        ]);

        self::assertTrue($providers['faker']);
        self::assertSame(class_exists(SetonoDAOBundle::class), $providers['dao']);
        self::assertSame(class_exists(SetonoGlsWebserviceBundle::class), $providers['gls']);
        self::assertSame(class_exists(SetonoPostNordBundle::class), $providers['post_nord']);
    }

    public function testItMergesMultipleConfigurationArrays(): void
    {
        // The later array overrides gls while leaving the faker value from the
        // earlier array untouched.
        $providers = $this->processProviders([
            ['providers' => ['faker' => true, 'gls' => true]],
            ['providers' => ['gls' => false]],
        ]);

        self::assertTrue($providers['faker']);
        self::assertFalse($providers['gls']);
    }
}
