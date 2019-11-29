<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Jobcloud\KafkaSchemaRegistryClient\Interfaces\SchemaRegistryClientInterface;

class SchemaRegistryApi
{
    /**
     * @var SchemaRegistryClientInterface
     */
    private $registryClient;

    /**
     * SchemaRegistryApi constructor.
     * @param SchemaRegistryClientInterface $schemaRegistryClient
     */
    public function __construct(SchemaRegistryClientInterface $schemaRegistryClient)
    {
        $this->registryClient = $schemaRegistryClient;
    }

    /**
     * @return array
     */
    public function getAllSchemas(): array
    {
        return $this->registryClient->call('GET', 'subjects') ?? [];
    }

}