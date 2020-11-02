<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ErrorHandlerInterface
{
    /**
     * @param ResponseInterface $response
     * @param string|null       $uri
     */
    public function handleError(
        ResponseInterface $response,
        string $uri = null,
        RequestInterface $request = null
    ): void;
}
