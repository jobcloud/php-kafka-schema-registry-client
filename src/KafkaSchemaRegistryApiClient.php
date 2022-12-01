<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\VersionNotFoundException;
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
     * @return array<string, mixed>|string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaDefinitionByVersion(string $subjectName, string $version = self::VERSION_LATEST)
    {
        return $this
            ->httpClient
            ->call(
                'GET',
                sprintf('subjects/%s/versions/%s/schema', $subjectName, $version)
            );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSchemaVersion(string $subjectName, string $version = self::VERSION_LATEST): ?int
    {
        return $this->httpClient->call('DELETE', sprintf('subjects/%s/versions/%s', $subjectName, $version));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaById(int $id): string
    {
        return $this->httpClient->call('GET', sprintf('schemas/ids/%s', $id))['schema'];
    }

    /**
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
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException|VersionNotFoundException
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
                   );
        } catch (SubjectNotFoundException | VersionNotFoundException $e) {
            if ($e instanceof VersionNotFoundException && self::VERSION_LATEST !== $version) {
                throw $e;
            }

            return true;
        }

        return ($results['is_compatible'] ?? false) === true;
    }

    /**
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
                    );

            return $results['version'] ?? null;
        } catch (SubjectNotFoundException $e) {
            return null;
        } catch (SchemaNotFoundException $e) {
            return null;
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function isSchemaAlreadyRegistered(string $subjectName, string $schema): bool
    {
        return null !== $this->getVersionForSchema($subjectName, $schema);
    }

    /**
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
     * @return array<string,mixed>
     * @throws JsonException
     */
    private function createRequestBodyFromSchema(string $schema): array
    {
        return ['schema' => json_encode(
            json_decode($schema, true, 512, JSON_THROW_ON_ERROR),
            JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION
        )];
    }
}
