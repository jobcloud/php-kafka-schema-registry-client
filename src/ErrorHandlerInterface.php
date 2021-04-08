<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use JsonException;

interface ErrorHandlerInterface
{
    /**
     * @param ResponseInterface $response
     * @param string|null       $uri
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function handleError(
        ResponseInterface $response,
        string $uri = null,
        RequestInterface $request = null
    ): void;
}
