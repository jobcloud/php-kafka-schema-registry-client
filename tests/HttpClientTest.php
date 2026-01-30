<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use Buzz\Client\Curl;
use Exception;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandler;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[CoversClass(HttpClient::class)]
class HttpClientTest extends TestCase
{
    use ReflectionAccessTrait;

    public function testCreateRequest(): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url'
        );

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri']);

        $this->assertSame('application/vnd.schemaregistry.v1+json', $response->getHeader('Content-Type')[0]);
        $this->assertSame('application/vnd.schemaregistry.v1+json', $response->getHeader('Accept')[0]);
        $this->assertSame('http', $response->getUri()->getScheme());
        $this->assertSame('some-url', $response->getUri()->getHost());
        $this->assertSame('/uri', $response->getUri()->getPath());
        $this->assertSame('', $response->getUri()->getQuery());
    }

    #[DataProvider('requestBodyDataProvider')]
    public function testCreateRequestWithBody(array $body, string $expectedEncodedBody): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url/'
        );

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri', $body]);
        $response->getBody()->rewind();

        $this->assertSame($expectedEncodedBody, $response->getBody()->getContents());
    }

    public function testCreateRequestWithQueryString(): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url'
        );

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri', [], ['a' => 'b']]);

        $this->assertSame('/uri', $response->getUri()->getPath());
        $this->assertSame('a=b', $response->getUri()->getQuery());
    }

    public function testCreateRequestWithAuthentication(): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url',
            'some-username',
            'some-password'
        );

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri']);

        $this->assertSame('Basic c29tZS11c2VybmFtZTpzb21lLXBhc3N3b3Jk', $response->getHeader('Authorization')[0]);
    }

    public function testCreateRequestWithAuthenticationWithoutUsername(): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url',
            'some-username'
        );

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri']);

        $this->assertArrayNotHasKey('Authorization', $response->getHeaders());
    }

    public function testCallMethod(): void
    {
        $clientMock = $this
            ->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendRequest'])
            ->getMock();

        $responseMock = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBody'])
            ->getMock();

        $stream = $this
            ->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__toString'])
            ->getMock();
        $stream->expects(self::atLeastOnce())->method('__toString')->willReturn('[1,2,3]');

        $responseMock->expects(self::atLeastOnce())->method('getBody')->willReturn($stream);
        $clientMock->expects(self::once())->method('sendRequest')->willReturn($responseMock);

        $httpClient = new HttpClient(
            $clientMock,
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url',
            'some-username'
        );

        $response = $httpClient->call('GET', 'uri');
        $this->assertSame([1,2,3], $response);
    }

    public function testCallMethodWithThrownException(): void
    {
        $clientMock = $this
            ->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendRequest'])
            ->getMock();

        $responseMock = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBody'])
            ->getMock();

        $stream = $this
            ->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__toString'])
            ->getMock();

        $stream->expects(self::atLeastOnce())->method('__toString')->willReturn('{"error_code": 404}');
        $responseMock->expects(self::atLeastOnce())->method('getBody')->willReturn($stream);
        $clientMock->expects(self::once())->method('sendRequest')->willReturn($responseMock);

        $httpClient = new HttpClient($clientMock, new Psr17Factory(), new ErrorHandler(), 'http://some-url');

        $this->expectException(Exception::class);
        $httpClient->call('GET', 'uri');
    }

    public static function requestBodyDataProvider(): array
    {
        return [
            [['a' => 'b'], '{"a":"b"}'],
            [['a' => 0.0], '{"a":0.0}'],
            [['a' => '{"b":0.0}'], '{"a":"{\"b\":0.0}"}'],
        ];
    }
}
