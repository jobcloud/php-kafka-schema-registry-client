<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;

interface HttpClientInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param array<string,mixed> $body
     * @param array<string,mixed> $queryParams
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function call(string $method, string $uri, array $body = [], array $queryParams = []);
}
