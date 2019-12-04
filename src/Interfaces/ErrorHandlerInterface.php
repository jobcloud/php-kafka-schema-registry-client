<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Interfaces;

interface ErrorHandlerInterface
{
    public function handleResponseData(string $errorCode, string $errorMessage): void;

}