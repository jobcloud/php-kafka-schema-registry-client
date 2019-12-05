<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Psr\Http\Message\ResponseInterface;

interface ErrorHandlerInterface
{
    /**
     * @param ResponseInterface $response
     */
    public function handleError(ResponseInterface $response): void;
}
