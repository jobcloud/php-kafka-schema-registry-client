<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Interfaces;

interface ErrorHandlerInterface
{
    /**
     * @param array $data
     */
    public function handleFromResponseData(array $data): void;
}
