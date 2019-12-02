<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Interfaces;

interface SchemaRegistryHttpClientInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @param array|null $body
     * @param array $queryParams
     * @return array|null
     */
    public function call(string $method, string $uri, ?array $body = null, array $queryParams = []): ?array;
}