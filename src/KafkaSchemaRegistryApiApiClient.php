<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

use Buzz\Exception\RequestException;
use Exception;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\SubjectNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\HttpClientInterface;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\KafkaSchemaRegistryApiClientInterface;

class KafkaSchemaRegistryApiApiClient implements KafkaSchemaRegistryApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $registryClient;

    /**
     * KafkaSchemaRegistryApi constructor.
     * @param HttpClientInterface $schemaRegistryClient
     */
    public function __construct(HttpClientInterface $schemaRegistryClient)
    {
        $this->registryClient = $schemaRegistryClient;
    }

    /**
     * @return array
     */
    public function getAllSubjects(): array
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
    public function getSchemaByVersion(string $subjectName, string $version = self::VERSION_LATEST): array
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
    public function registerNewSchemaVersion(string $subjectName, string $schema): array
    {
        return $this
                ->registryClient
                ->call(
                    'POST',
                    sprintf('/subjects/%s/versions/', $subjectName),
                    $this->prepareSchemaData($schema)
                ) ?? [];
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @param string $version
     * @return bool
     * @throws Exception
     */
    public function checkSchemaCompatibilityForVersion(
        string $subjectName,
        string $schema,
        string $version = self::VERSION_LATEST
    ): bool {

        try {
            $results = $this
                   ->registryClient
                   ->call(
                       'POST',
                       sprintf('/compatibility/subjects/%s/versions/%s', $subjectName, $version),
                       $this->prepareSchemaData($schema)
                   ) ?? [];
        } catch (SubjectNotFoundException $e) {
            return true;
        }

        return (bool) $results['is_compatible'];
    }

    /**
     * @param string $subjectName
     * @return string|null
     * @throws Exception
     */
    public function getSubjectCompatibilityLevel(string $subjectName): ?string
    {
        try {
            $results = $this->registryClient->call('GET', sprintf('/config/%s', $subjectName));
            return $results['compatibilityLevel'];
        } catch (SubjectNotFoundException $e) {
            return $this->getDefaultCompatibilityLevel();
        }
    }

    /**
     * @param string $subjectName
     * @param string $level
     * @return bool
     */
    public function setSubjectCompatibilityLevel(string $subjectName, string $level = self::LEVEL_FULL): bool {

        $this->registryClient->call(
            'PUT',
            sprintf('config/%s', $subjectName),
            ['compatibility' => $level]
        );

        return true;
    }

    /**
     * @return string
     */
    public function getDefaultCompatibilityLevel(): string {

        $results = $this->registryClient->call('GET', 'config');

        return $results['compatibilityLevel'];
    }

    /**
     * @param string $level
     * @return bool
     */
    public function setDefaultCompatibilityLevel(string $level = self::LEVEL_FULL): bool {
        $this->registryClient->call('PUT', 'config', ['compatibility' => $level]);
        return true;
    }

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string
    {
        try {
            $results = $this
                    ->registryClient
                    ->call(
                        'POST',
                        sprintf('/subjects/%s/', $subjectName),
                        $this->prepareSchemaData($schema)
                    ) ?? [];

            return (string) $results['version'];
        } catch (SubjectNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param string $subjectName
     * @return array
     */
    public function deleteSubject(string $subjectName): array
    {
        return $this
            ->registryClient
            ->call(
                'DELETE',
                sprintf('/subjects/%s/', $subjectName)
            ) ?? [];
    }

    /**
     * @param string $subjectName
     * @return string|null
     * @throws RequestException
     */
    public function getLatestSubjectVersion(string $subjectName): ?string
    {
        try {
            $schemaVersions = $this->getAllSubjectVersions($subjectName);
            $lastKey = array_key_last($schemaVersions);
            return $schemaVersions[$lastKey];
        } catch (SubjectNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param string $schema
     * @return array
     */
    private function prepareSchemaData(string $schema): array
    {
        return ['schema' => json_encode(json_decode($schema, true))];
    }
}
