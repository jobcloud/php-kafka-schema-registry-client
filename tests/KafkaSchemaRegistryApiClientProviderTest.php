<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandlerInterface;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use LogicException;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @covers \Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider
 */
class KafkaSchemaRegistryApiClientProviderTest extends TestCase
{
    use ReflectionAccessTrait;

    public function testDefaultContainersAndServicesSetWithMinimalConfig(): void
    {
        $container = new Container();

        $this->assertArrayNotHasKey('kafka.schema.registry', $container);
        $this->assertArrayNotHasKey('username', $container['kafka.schema.registry'] ?? []);
        $this->assertArrayNotHasKey('password', $container['kafka.schema.registry'] ?? []);
        $this->assertArrayNotHasKey('base.url', $container['kafka.schema.registry'] ?? []);
        $this->assertArrayNotHasKey('kafka.schema.registry.client', $container);
        $this->assertArrayNotHasKey('kafka.schema.registry.request.factory', $container);
        $this->assertArrayNotHasKey('kafka.schema.registry.client.http', $container);
        $this->assertArrayNotHasKey('kafka.schema.registry.client.api', $container);
        $this->assertArrayNotHasKey('kafka.schema.registry.error.handler', $container);

        $container['kafka.schema.registry'] = [
            'base.url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());

        $this->assertArrayHasKey('kafka.schema.registry', $container);
        $this->assertArrayHasKey('username', $container['kafka.schema.registry']);
        $this->assertArrayHasKey('password', $container['kafka.schema.registry']);
        $this->assertArrayHasKey('kafka.schema.registry.client', $container);
        $this->assertArrayHasKey('kafka.schema.registry.request.factory', $container);
        $this->assertArrayHasKey('kafka.schema.registry.client.http', $container);
        $this->assertArrayHasKey('kafka.schema.registry.client.api', $container);
        $this->assertArrayHasKey('kafka.schema.registry.error.handler', $container);

        $this->assertInstanceOf(RequestFactoryInterface::class, $container['kafka.schema.registry.request.factory']);
        $this->assertInstanceOf(HttpClientInterface::class, $container['kafka.schema.registry.client.http']);
        $this->assertInstanceOf(ErrorHandlerInterface::class, $container['kafka.schema.registry.error.handler']);
        $this->assertInstanceOf(
            KafkaSchemaRegistryApiClientInterface::class,
            $container['kafka.schema.registry.client.api']
        );

    }

    public function testFailOnMissingBaseUrlInContainer(): void
    {
        $container = new Container();

        $container['kafka.schema.registry'] = [
            'username' => 'u1',
            'password' => 'p1',
        ];

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing schema registry URL, please set it under "base.url" container offset');

        $container->register(new KafkaSchemaRegistryApiClientProvider());
    }

    public function testUserNameAndPasswordFromSettingsArePassedToHttpClient(): void
    {
        $container = new Container();

        $container['kafka.schema.registry'] = [
            'base.url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());
        $this->assertSame(
            $container['kafka.schema.registry']['username'],
            $this->getPropertyValue($container['kafka.schema.registry.client.http'], 'username')
        );

        $this->assertSame(
            $container['kafka.schema.registry']['password'],
            $this->getPropertyValue($container['kafka.schema.registry.client.http'], 'password')
        );

    }
}
