<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Interfaces;

interface KafkaSchemaRegistryClientInterface
{

    /**
     * @return array
     */
    public function getAllSubjects(): array;

    /**
     * @param string $subjectName
     * @return array
     */
    public function getAllSubjectVersions(string $subjectName): array;

    /**
     * @param string $subjectName
     * @param string $version
     * @return array
     */
    public function getSchemaByVersion(string $subjectName, string $version = 'latest'): array;

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
    public function getSubjectCompatibilityForVersion(string $subjectName): string;

    /**
     * @param string $schema
     * @param string $subjectName
     * @param string $version
     * @return string
     */
    public function getDefaultCompatibilityLevel(
        string $schema,
        string $subjectName,
        string $version = 'latest'
    ): string;

    /**
     * @param string $subjectName
     * @param string $schema
     * @return string|null
     */
    public function getVersionForSchema(string $subjectName, string $schema): ?string;

    /**
     * @param string $subjectName
     * @return array
     */
    public function deleteSubject(string $subjectName): array;

    /**
     * @param string $subjectName
     * @return string|null
     */
    public function getLatestSubjectVersion(string $subjectName): ?string;

}