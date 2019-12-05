<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

interface HttpClientInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $body
     * @param array $queryParams
     * @return array|null
     */
    public function call(string $method, string $uri, array $body = [], array $queryParams = []): ?array;
}
