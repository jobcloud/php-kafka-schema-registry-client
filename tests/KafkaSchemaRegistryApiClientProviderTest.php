<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Tests;

use Jobcloud\KafkaSchemaRegistryClient\ErrorHandlerInterface;
use Jobcloud\KafkaSchemaRegistryClient\HttpClientInterface;
use Jobcloud\KafkaSchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\KafkaSchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use LogicException;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Http\Message\RequestFactoryInterface;

class KafkaSchemaRegistryApiClientProviderTest extends TestCase
{
    use ReflectionAccessTrait;

    public function testDefaultContainersAndServicesSetWithMinimalConfig(): void
    {
        $container = new Container();

        $this->assertArrayNotHasKey('kafka_schema_registry', $container);
        $this->assertArrayNotHasKey('username', $container['kafka_schema_registry'] ?? []);
        $this->assertArrayNotHasKey('password', $container['kafka_schema_registry'] ?? []);
        $this->assertArrayNotHasKey('base_url', $container['kafka_schema_registry'] ?? []);
        $this->assertArrayNotHasKey('_client', $container);
        $this->assertArrayNotHasKey('_request_factory', $container);
        $this->assertArrayNotHasKey('_http_client', $container);
        $this->assertArrayNotHasKey('_api_client', $container);
        $this->assertArrayNotHasKey('_error_handler', $container);

        $container['kafka_schema_registry'] = [
            'base_url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());

        $this->assertArrayHasKey('kafka_schema_registry', $container);
        $this->assertArrayHasKey('username', $container['kafka_schema_registry']);
        $this->assertArrayHasKey('password', $container['kafka_schema_registry']);
        $this->assertArrayHasKey('_client', $container);
        $this->assertArrayHasKey('_request_factory', $container);
        $this->assertArrayHasKey('_http_client', $container);
        $this->assertArrayHasKey('_api_client', $container);
        $this->assertArrayHasKey('_error_handler', $container);

        $this->assertInstanceOf(RequestFactoryInterface::class, $container['_request_factory']);
        $this->assertInstanceOf(HttpClientInterface::class, $container['_http_client']);
        $this->assertInstanceOf(ErrorHandlerInterface::class, $container['_error_handler']);
        $this->assertInstanceOf(
            KafkaSchemaRegistryApiClientInterface::class,
            $container['_api_client']
        );

    }

    public function testFailOnMissingBaseUrlInContainer(): void
    {
        $container = new Container();

        $container['kafka_schema_registry'] = [
            'username' => 'u1',
            'password' => 'p1',
        ];

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing schema registry URL, please set it under "base_url" container offset');

        $container->register(new KafkaSchemaRegistryApiClientProvider());
    }

    public function testUserNameAndPasswordFromSettingsArePassedToHttpClient(): void
    {
        $container = new Container();

        $container['kafka_schema_registry'] = [
            'base_url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());
        $this->assertSame(
            $container['kafka_schema_registry']['username'],
            $this->getPropertyValue($container['_http_client'], 'username')
        );

        $this->assertSame(
            $container['kafka_schema_registry']['password'],
            $this->getPropertyValue($container['_http_client'], 'password')
        );

    }
}