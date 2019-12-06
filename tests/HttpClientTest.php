<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Tests;

use Buzz\Client\Curl;
use Exception;
use Jobcloud\KafkaSchemaRegistryClient\ErrorHandler;
use Jobcloud\KafkaSchemaRegistryClient\HttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


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

    public function testCreateRequestWithBody(): void
    {
        $httpClient = new HttpClient(
            new Curl(new Psr17Factory()),
            new Psr17Factory(),
            new ErrorHandler(),
            'http://some-url/'
        );

        $body = ['a' => 'b'];
        $jsonEncodedBody = json_encode($body);

        /** @var RequestInterface $response */
        $response = $this->invokeMethod($httpClient, 'createRequest', ['GET', 'uri', $body]);
        $response->getBody()->rewind();

        $this->assertSame('9', $response->getHeader('Content-Length')[0]);
        $this->assertSame($jsonEncodedBody, $response->getBody()->read(9));
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
            ->getMockBuilder(ResponseInterface::class)
            ->onlyMethods(['getBody'])
            ->getMockForAbstractClass();

        $stream = $this->getMockBuilder(StreamInterface::class)->onlyMethods(['__toString'])->getMockForAbstractClass();
        $stream->method('__toString')->willReturn('[1,2,3]');

        $responseMock->method('getBody')->willReturn($stream);
        $clientMock->method('sendRequest')->willReturn($responseMock);

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
            ->getMockBuilder(ResponseInterface::class)
            ->onlyMethods(['getBody'])
            ->getMockForAbstractClass();

        $stream = $this->getMockBuilder(StreamInterface::class)->onlyMethods(['__toString'])->getMockForAbstractClass();

        $stream->method('__toString')->willReturn('{"error_code": 404}');
        $responseMock->method('getBody')->willReturn($stream);
        $clientMock->method('sendRequest')->willReturn($responseMock);

        $httpClient = new HttpClient($clientMock, new Psr17Factory(), new ErrorHandler(), 'http://some-url');

        $this->expectException(Exception::class);
        $httpClient->call('GET', 'uri');
    }
}