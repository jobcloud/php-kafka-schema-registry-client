<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use Buzz\Client\Curl;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandlerInterface;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use phpmock\phpunit\MockObjectProxy;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Http\Message\RequestFactoryInterface;

#[CoversClass(KafkaSchemaRegistryApiClientProvider::class)]
class KafkaSchemaRegistryApiClientProviderTest extends TestCase
{
    use PHPMock;
    use ReflectionAccessTrait;

    /**
     * @var MockObject|MockObjectProxy
     */
    private $classExistsMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classExistsMock = $this->getFunctionMock(
            'Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider',
            'class_exists'
        );
    }

    public function testDefaultContainersAndServicesSetWithMinimalConfig(): void
    {
        $this->classExistsMock->expects(self::exactly(2))
            ->with(self::logicalOr(
                self::equalTo(Psr17Factory::class),
                self::equalTo(Curl::class)
            ))
            ->willReturn(true);

        $container = new Container();

        self::assertArrayNotHasKey('kafka.schema.registry', $container);
        self::assertArrayNotHasKey('username', $container['kafka.schema.registry'] ?? []);
        self::assertArrayNotHasKey('password', $container['kafka.schema.registry'] ?? []);
        self::assertArrayNotHasKey('base.url', $container['kafka.schema.registry'] ?? []);
        self::assertArrayNotHasKey('kafka.schema.registry.client', $container);
        self::assertArrayNotHasKey('kafka.schema.registry.request.factory', $container);
        self::assertArrayNotHasKey('kafka.schema.registry.client.http', $container);
        self::assertArrayNotHasKey('kafka.schema.registry.client.api', $container);
        self::assertArrayNotHasKey('kafka.schema.registry.error.handler', $container);

        $container['kafka.schema.registry'] = [
            'base.url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());

        self::assertArrayHasKey('kafka.schema.registry', $container);
        self::assertArrayHasKey('username', $container['kafka.schema.registry']);
        self::assertArrayHasKey('password', $container['kafka.schema.registry']);
        self::assertArrayHasKey('kafka.schema.registry.client', $container);
        self::assertArrayHasKey('kafka.schema.registry.request.factory', $container);
        self::assertArrayHasKey('kafka.schema.registry.client.http', $container);
        self::assertArrayHasKey('kafka.schema.registry.client.api', $container);
        self::assertArrayHasKey('kafka.schema.registry.error.handler', $container);

        $client = $container['kafka.schema.registry.client.http'];

        self::assertInstanceOf(RequestFactoryInterface::class, $container['kafka.schema.registry.request.factory']);
        self::assertInstanceOf(HttpClientInterface::class, $client);
        self::assertInstanceOf(ErrorHandlerInterface::class, $container['kafka.schema.registry.error.handler']);
        self::assertInstanceOf(
            KafkaSchemaRegistryApiClientInterface::class,
            $container['kafka.schema.registry.client.api']
        );
    }

    public function testSuccessWithMissingAuth(): void
    {
        $this->classExistsMock
            ->expects(self::exactly(2))
            ->with(self::logicalOr(
                self::equalTo(Psr17Factory::class),
                self::equalTo(Curl::class)
            ))
            ->willReturn(true);

        $container = new Container();

        $container['kafka.schema.registry'] = [
            'base.url' => 'http://some-url'
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());

        $client = $container['kafka.schema.registry.client.http'];

        self::assertInstanceOf(HttpClientInterface::class, $client);
        self::assertNull(self::getPropertyValue($client, 'username'));
        self::assertNull(self::getPropertyValue($client, 'password'));
    }

    public function testFailOnMissingBaseUrlInContainer(): void
    {
        $this->classExistsMock->expects(self::never());

        $container = new Container();

        $container['kafka.schema.registry'] = [
            'username' => 'u1',
            'password' => 'p1',
        ];

        self::expectException(LogicException::class);
        self::expectExceptionMessage('Missing schema registry URL, please set it under "base.url" container offset');

        $container->register(new KafkaSchemaRegistryApiClientProvider());
    }

    public function testUserNameAndPasswordFromSettingsArePassedToHttpClient(): void
    {
        $this->classExistsMock->expects(self::exactly(2))
            ->with(self::logicalOr(
                self::equalTo(Psr17Factory::class),
                self::equalTo(Curl::class)
            ))
            ->willReturn(true);

        $container = new Container();

        $container['kafka.schema.registry'] = [
            'base.url' => 'http://some-url',
            'username' => 'u1',
            'password' => 'p1',
        ];

        $container->register(new KafkaSchemaRegistryApiClientProvider());
        self::assertSame(
            $container['kafka.schema.registry']['username'],
            self::getPropertyValue($container['kafka.schema.registry.client.http'], 'username')
        );

        self::assertSame(
            $container['kafka.schema.registry']['password'],
            self::getPropertyValue($container['kafka.schema.registry.client.http'], 'password')
        );
    }
}
