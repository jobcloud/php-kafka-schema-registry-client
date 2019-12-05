<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

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

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param ErrorHandlerInterface $errorHandler
     * @param string $baseUrl
     * @param string|null $username
     * @param string|null $password
     */
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
     * @param string $method
     * @param string $uri
     * @param array $body
     * @param array $queryParams
     * @return RequestInterface
     */
    protected function createRequest(
        string $method,
        string $uri,
        array $body = [],
        array $queryParams = []
    ): RequestInterface {

        $queryString = 0 !== count($queryParams) ? '?' . http_build_query($queryParams) : null;

        // Ensures that there is no trailing slashes or double slashes in endpoint URL
        $url = $this->baseUrl . '/' . $uri . $queryString;

        $request = $this->requestFactory->createRequest($method, $url);

        if ([] !== $body) {
            $jsonData = json_encode($body, JSON_THROW_ON_ERROR);

            $request = $request->withAddedHeader('Content-Length', (string) strlen($jsonData));
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
     * @param string $method
     * @param string $uri
     * @param array $body
     * @param array $queryParams
     * @return array|null
     * @throws ClientExceptionInterface
     */
    public function call(string $method, string $uri, array $body = [], array $queryParams = []): ?array
    {
        $response = $this->client->sendRequest($this->createRequest($method, $uri, $body, $queryParams));

        $this->errorHandler->handleError($response);

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
