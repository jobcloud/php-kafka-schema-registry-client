<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Exception;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\SchemaRegistryClientInterface;
use ResourceNotFoundException;

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

    /**
     * @param string $subjectName
     * @return array
     */
    public function getAllSubjectVersions(string $subjectName): array
    {
        return $this
                ->registryClient
                ->call('GET', sprintf('/subjects/%s/versions', $subjectName)) ?? [];
    }

    /**
     * @param string $subjectName
     * @param string $version
     * @return array
     */
    public function getSchemaByVersion(string $subjectName, string $version = 'latest'): array
    {
        return $this
                ->registryClient
                ->call('GET', sprintf('/subjects/%s/versions/%s', $subjectName, $version)) ?? [];
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @return array
     */
    public function registerNewSchemaVersion(string $schema, string $subjectName): array
    {
        return $this
                ->registryClient
                ->call('POST', sprintf('/subjects/%s/versions/', $subjectName));
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @param string $version
     * @return bool
     * @throws Exception
     */
    public function checkSchemaCompatibilityForVersion(
        string $schema,
        string $subjectName,
        string $version = 'latest'
    ): bool
    {

       try{
           $results = $this
                   ->registryClient
                   ->call(
                       'POST',
                       sprintf('/compatibility/subjects/%s/versions/%s', $subjectName, $version),
                       $this->prepareSchemaData($schema)
                   ) ?? [];
       } catch (ResourceNotFoundException $e) {
           return true;
       }

        return (bool) $results['is_compatible'];
    }

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string {
        try{
            $results = $this
                    ->registryClient
                    ->call(
                        'POST',
                        sprintf('/subjects/%s/', $subjectName),
                        $this->prepareSchemaData($schema)
                    ) ?? [];

            return (string) $results['version'];
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param string $subjectName
     * @return array
     */
    public function deleteSchema(string $subjectName): array {
        return $this
            ->registryClient
            ->call(
                'DELETE',
                sprintf('/subjects/%s/', $subjectName)
            ) ?? [];
    }

    /**
     * @param string $schema
     * @return array
     */
    private function prepareSchemaData(string $schema): array
    {
        $decoded = json_decode($schema, true);

        if (is_array($decoded) && array_key_exists('schema', $decoded)) {
            return json_encode($decoded);
        }

        return ['schema' => json_encode($decoded)];
    }

}