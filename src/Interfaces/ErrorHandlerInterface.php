<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Interfaces;

interface ErrorHandlerInterface
{
    /**
     * @param array $data
     */
    public function handleError(array $data): void;
}
