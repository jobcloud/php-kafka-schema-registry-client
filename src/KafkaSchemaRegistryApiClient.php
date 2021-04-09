<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SubjectNotFoundException;
use JsonException;

class KafkaSchemaRegistryApiClient implements KafkaSchemaRegistryApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * KafkaSchemaRegistryApi constructor.
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSubjects(): array
    {
        return $this->httpClient->call('GET', 'subjects') ?? [];
    }

    /**
     * @param string $subjectName
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getAllSubjectVersions(string $subjectName): array
    {
        return $this->httpClient->call('GET', sprintf('subjects/%s/versions', $subjectName)) ?? [];
    }

    /**
     * @param string $subjectName
     * @param string $version
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaByVersion(string $subjectName, string $version = self::VERSION_LATEST): array
    {
        return $this
                ->httpClient
                ->call('GET', sprintf('subjects/%s/versions/%s', $subjectName, $version)) ?? [];
    }

    /**
     * @param string $subjectName
     * @param string $version
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaDefinitionByVersion(string $subjectName, string $version = self::VERSION_LATEST): array
    {
        return $this
            ->httpClient
            ->call(
                'GET',
                sprintf('subjects/%s/versions/%s/schema', $subjectName, $version)
            ) ?? [];
    }

    /**
     * @param string $subjectName
     * @param string $version
     * @return int|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSchemaVersion(string $subjectName, string $version = self::VERSION_LATEST): ?int
    {
        return $this->httpClient->call('DELETE', sprintf('subjects/%s/versions/%s', $subjectName, $version));
    }

    /**
     * @param int $id
     * @return string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaById(int $id): string
    {
        return $this->httpClient->call('GET', sprintf('schemas/ids/%s', $id))['schema'];
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function registerNewSchemaVersion(string $subjectName, string $schema): array
    {
        return $this
                ->httpClient
                ->call(
                    'POST',
                    sprintf('subjects/%s/versions', $subjectName),
                    $this->createRequestBodyFromSchema($schema)
                ) ?? [];
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @param string $version
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function checkSchemaCompatibilityForVersion(
        string $subjectName,
        string $schema,
        string $version = self::VERSION_LATEST
    ): bool {
        try {
            $results = $this
                   ->httpClient
                   ->call(
                       'POST',
                       sprintf('compatibility/subjects/%s/versions/%s', $subjectName, $version),
                       $this->createRequestBodyFromSchema($schema)
                   ) ?? [];
        } catch (SubjectNotFoundException $e) {
            return true;
        }

        return $results['is_compatible'] === true;
    }

    /**
     * @param string $subjectName
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSubjectCompatibilityLevel(string $subjectName): ?string
    {
        try {
            $results = $this->httpClient->call('GET', sprintf('config/%s', $subjectName));
            return $results['compatibilityLevel'];
        } catch (SubjectNotFoundException $e) {
            return $this->getDefaultCompatibilityLevel();
        }
    }

    /**
     * @param string $subjectName
     * @param string $level
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setSubjectCompatibilityLevel(string $subjectName, string $level = self::LEVEL_FULL): bool
    {
        $this->httpClient->call('PUT', sprintf('config/%s', $subjectName), ['compatibility' => $level]);
        return true;
    }

    /**
     * @return string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getDefaultCompatibilityLevel(): string
    {
        $results = $this->httpClient->call('GET', 'config');
        return $results['compatibilityLevel'];
    }

    /**
     * @param string $level
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setDefaultCompatibilityLevel(string $level = self::LEVEL_FULL): bool
    {
        $this->httpClient->call('PUT', 'config', ['compatibility' => $level]);
        return true;
    }

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string
    {
        try {
            $results = $this
                    ->httpClient
                    ->call(
                        'POST',
                        sprintf('subjects/%s', $subjectName),
                        $this->createRequestBodyFromSchema($schema)
                    ) ?? [];

            return (string) $results['version'];
        } catch (SubjectNotFoundException $e) {
            return null;
        } catch (SchemaNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param string $subjectName
     * @param string $schema
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function isSchemaAlreadyRegistered(string $subjectName, string $schema): bool
    {
        return null !== $this->getVersionForSchema($subjectName, $schema);
    }

    /**
     * @param string $subjectName
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSubject(string $subjectName): array
    {
        return $this->httpClient->call('DELETE', sprintf('subjects/%s', $subjectName)) ?? [];
    }

    /**
     * @param string $subjectName
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getLatestSubjectVersion(string $subjectName): ?string
    {
        $schemaVersions = $this->getAllSubjectVersions($subjectName);
        $lastKey = array_key_last($schemaVersions);
        return $schemaVersions[$lastKey];
    }

    /**
     * @param string $mode
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setImportMode(string $mode): bool
    {
        $result = $this->httpClient->call('PUT', 'mode/', ['mode' => $mode]);
        return $result === ['mode' => $mode];
    }

    /**
     * @param string $schema
     * @return array<string,mixed>
     * @throws JsonException
     */
    private function createRequestBodyFromSchema(string $schema): array
    {
        return ['schema' => json_encode(json_decode($schema, true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR)];
    }
}
