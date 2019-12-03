<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Jobcloud\KafkaSchemaRegistryClient\Exceptions\ClientException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\ResourceNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\UnauthorizedException;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\HttpClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param string $baseUrl
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $baseUrl,
        ?string $username = null,
        ?string $password = null
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    protected function parseJsonResponse(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $body
     * @param array  $queryParams
     * @return RequestInterface
     */
    protected function createRequest(
        string $method,
        string $uri,
        ?array $body = [],
        array $queryParams = []
    ): RequestInterface {

        $queryString = 0 !== count($queryParams) ? '?' . http_build_query($queryParams) : null;

        // Ensures that there is no trailing slashes or double slashes in endpoint URL
        $url = trim($this->baseUrl, '/') . '/' . trim($uri, '/') . $queryString;

        $request = $this->requestFactory->createRequest($method, $url);

        if (null !== $body && [] !== $body) {
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
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/vnd.schemaregistry.v1+json');
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|null $body
     * @param array $queryParams
     * @return array|null
     * @throws ClientException
     * @throws ClientExceptionInterface
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    public function call(string $method, string $uri, ?array $body = null, array $queryParams = []): ?array
    {
        $response = $this->client->sendRequest($this->createRequest($method, $uri, $body, $queryParams));
        $responseData = $this->parseJsonResponse($response);

        $this->parseForErrors($responseData);

        return $responseData;
    }

    /**
     * @param array $responseData
     * @return void
     * @throws ClientException
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    protected function parseForErrors(array $responseData): void
    {
        if (false === isset($responseData['error_code'])) {
            return;
        }

        $errorCode = $responseData['error_code'];
        $errorMessage = $responseData['message'] ?? '';

        switch ($errorCode) {
            case 40402:
            case 404:
                throw new ResourceNotFoundException($errorMessage);
            case 401:
                throw new UnauthorizedException($errorMessage);
            default:
                throw new ClientException($errorMessage);
        }
    }
}
