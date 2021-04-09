<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use JsonException;

interface KafkaSchemaRegistryApiClientInterface
{
    public const VERSION_LATEST = 'latest';

    public const LEVEL_BACKWARD = 'BACKWARD';
    public const LEVEL_BACKWARD_TRANSITIVE = 'BACKWARD_TRANSITIVE';
    public const LEVEL_FORWARD = 'FORWARD';
    public const LEVEL_FORWARD_TRANSITIVE = 'FORWARD_TRANSITIVE';
    public const LEVEL_FULL = 'FULL';
    public const LEVEL_FULL_TRANSITIVE = 'FULL_TRANSITIVE';
    public const LEVEL_NONE = 'NONE';

    public const MODE_IMPORT = 'IMPORT';
    public const MODE_READONLY = 'READONLY';
    public const MODE_READWRITE = 'READWRITE';

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSubjects(): array;

    /**
     * @param string $subjectName
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getAllSubjectVersions(string $subjectName): array;

    /**
     * @param string $subjectName
     * @param string $version
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaByVersion(string $subjectName, string $version = 'latest'): array;

    /**
     * @param string $subjectName
     * @param string $version
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaDefinitionByVersion(string $subjectName, string $version = self::VERSION_LATEST): array;

    /**
     * @param string $subjectName
     * @param string $version
     * @return int|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSchemaVersion(string $subjectName, string $version = self::VERSION_LATEST): ?int;

    /**
     * @param int $id
     * @return string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaById(int $id): string;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function registerNewSchemaVersion(string $subjectName, string $schema): array;

    /**
     * @param string $subjectName
     * @param string $schema
     * @param string $version
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function checkSchemaCompatibilityForVersion(
        string $subjectName,
        string $schema,
        string $version = 'latest'
    ): bool;

    /**
     * @param string $subjectName
     * @return string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSubjectCompatibilityLevel(string $subjectName): ?string;

    /**
     * @param string $subjectName
     * @param string $level
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setSubjectCompatibilityLevel(string $subjectName, string $level = self::LEVEL_FULL): bool;

    /**
     * @return string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getDefaultCompatibilityLevel(): string;

    /**
     * @param string $level
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setDefaultCompatibilityLevel(string $level = self::LEVEL_FULL): bool;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function isSchemaAlreadyRegistered(string $subjectName, string $schema): bool;

    /**
     * @param string $subjectName
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSubject(string $subjectName): array;

    /**
     * @param string $mode
     * @return bool
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setImportMode(string $mode): bool;

    /**
     * @param string $subjectName
     * @return string|null
     * @throws JsonException
     */
    public function getLatestSubjectVersion(string $subjectName): ?string;
}
