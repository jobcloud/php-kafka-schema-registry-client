<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use JsonException;

class HttpClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        ErrorHandlerInterface $errorHandler,
        string $baseUrl,
        ?string $username = null,
        ?string $password = null
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->requestFactory = $requestFactory;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param array<string,mixed> $body
     * @param array<string,mixed> $queryParams
     * @throws JsonException
     */
    private function createRequest(
        string $method,
        string $uri,
        array $body = [],
        array $queryParams = []
    ): RequestInterface {

        $queryString = 0 !== count($queryParams) ? '?' . http_build_query($queryParams) : '';
        var_dump($queryString);

        $url = $this->baseUrl . '/' . $uri . $queryString;

        $request = $this->requestFactory->createRequest($method, $url);

        if ([] !== $body) {
            $jsonData = json_encode($body, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);

            $dataLength = strlen($jsonData);

            $request = $request->withAddedHeader('Content-Length', (string) $dataLength);
            $request->getBody()->write($jsonData);
        }

        if (null !== $this->username && null !== $this->password) {
            $request = $request->withHeader(
                'Authorization',
                sprintf('Basic %s', base64_encode(sprintf('%s:%s', $this->username, $this->password)))
            );
        }

        return $request
            ->withHeader('Content-Type', 'application/vnd.schemaregistry.v1+json')
            ->withHeader('Accept', 'application/vnd.schemaregistry.v1+json');
    }

    /**
     * @param array<string,mixed> $body
     * @param array<string,mixed> $queryParams
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function call(string $method, string $uri, array $body = [], array $queryParams = [])
    {
        $request = $this->createRequest($method, $uri, $body, $queryParams);

        $response = $this->client->sendRequest($request);

        $this->errorHandler->handleError($response, $uri, $request);

        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
