<?php

namespace Jobcloud\KafkaSchemaRegistryClient;

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

    /**
     * @return array
     */
    public function getSubjects(): array;

    /**
     * @param string $subjectName
     * @return array
     */
    public function getAllSubjectVersions(string $subjectName): array;

    /**
     * @param string $subjectName
     * @param string $version
     * @return string
     */
    public function getSchemaByVersion(string $subjectName, string $version = 'latest'): string;

    /**
     * @param string $subjectName
     * @param string $version
     * @return bool
     */
    public function deleteSchemaVersion(string $subjectName, string $version = self::VERSION_LATEST): bool;

    /**
     * @param int $id
     * @return string
     */
    public function getSchemaById(int $id): string;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return array
     */
    public function registerNewSchemaVersion(string $subjectName, string $schema): array;

    /**
     * @param string $subjectName
     * @param string $schema
     * @param string $version
     * @return bool
     */
    public function checkSchemaCompatibilityForVersion(
        string $subjectName,
        string $schema,
        string $version = 'latest'
    ): bool;

    /**
     * @param string $subjectName
     * @return string
     */
    public function getSubjectCompatibilityLevel(string $subjectName): ?string;

    /**
     * @param string $subjectName
     * @param string $level
     * @return bool
     */
    public function setSubjectCompatibilityLevel(string $subjectName, string $level = self::LEVEL_FULL): bool;

    /**
     * @return string
     */
    public function getDefaultCompatibilityLevel(): string;

    /**
     * @param string $level
     * @return bool
     */
    public function setDefaultCompatibilityLevel(string $level = self::LEVEL_FULL): bool;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string;

    /**
     * @param string $subjectName
     * @return bool
     */
    public function deleteSubject(string $subjectName): bool;

    /**
     * @param string $subjectName
     * @return string|null
     */
    public function getLatestSubjectVersion(string $subjectName): ?string;
}
