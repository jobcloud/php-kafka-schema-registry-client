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
    public function getSubjects(string $includeDeleted = 'false'): array;

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getAllSubjectVersions(string $subjectName): array;

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaByVersion(string $subjectName, string $version = 'latest'): array;

    /**
     * @return array<string, mixed>|string
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaDefinitionByVersion(string $subjectName, string $version = self::VERSION_LATEST);

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSchemaVersion(string $subjectName, string $version = self::VERSION_LATEST): ?int;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSchemaById(int $id): string;

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function registerNewSchemaVersion(string $subjectName, string $schema): array;

    /**
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
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getSubjectCompatibilityLevel(string $subjectName): ?string;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setSubjectCompatibilityLevel(string $subjectName, string $level = self::LEVEL_FULL): bool;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getDefaultCompatibilityLevel(): string;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setDefaultCompatibilityLevel(string $level = self::LEVEL_FULL): bool;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function isSchemaAlreadyRegistered(string $subjectName, string $schema): bool;

    /**
     * @return array<string,mixed>
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function deleteSubject(string $subjectName): array;

    /**
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function setImportMode(string $mode): bool;

    /**
     * @throws JsonException
     */
    public function getLatestSubjectVersion(string $subjectName): ?string;
}
