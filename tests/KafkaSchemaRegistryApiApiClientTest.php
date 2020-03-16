<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\ImportException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SubjectNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClient;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class KafkaSchemaRegistryApiApiClientTest extends TestCase
{
    private const TEST_SUBJECT_NAME = 'some-subject';
    private const TEST_SCHEMA = '{}';
    private const TEST_VERSION = 3;

    /**
     * @return MockObject|HttpClientInterface
     */
    private function getHttpClientMock(): MockObject
    {
        return $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();    
    }
    
    public function testGetSubjects(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock->expects($this->once())->method('call')->with('GET', 'subjects');

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $api->getSubjects();
    }

    public function testGetAllSubjectVersions(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('subjects/%s/versions', self::TEST_SUBJECT_NAME));

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $api->getAllSubjectVersions(self::TEST_SUBJECT_NAME);
    }

    public function testGetSchemaByVersion(): void
    {
        $httpClientMock = $this->getHttpClientMock();
        
        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('subjects/%s/versions/%s', self::TEST_SUBJECT_NAME, self::TEST_VERSION))
            ->willReturn(['schema' => '{}']);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getSchemaByVersion(self::TEST_SUBJECT_NAME, self::TEST_VERSION);

        $this->assertSame(['schema' => '{}'], $result);
    }

    public function testGetSchemaDefinitionByVersion(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('subjects/%s/versions/%s/schema', self::TEST_SUBJECT_NAME, self::TEST_VERSION))
            ->willReturn(['a' => 'b']);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getSchemaDefinitionByVersion(self::TEST_SUBJECT_NAME, self::TEST_VERSION);

        $this->assertSame(['a' => 'b'], $result);
    }

    public function testDeleteSchemaVersion(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('DELETE', sprintf('subjects/%s/versions/%s', self::TEST_SUBJECT_NAME, self::TEST_VERSION))
            ->willReturn(1);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->deleteSchemaVersion(self::TEST_SUBJECT_NAME, self::TEST_VERSION);

        $this->assertSame(1, $result);
    }

    public function testGetSchemaById(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('schemas/ids/%s', 1))
            ->willReturn(['schema' => '{}']);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $api->getSchemaById(1);
    }

    public function testRegisterNewSchemaVersion(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('POST', sprintf('subjects/%s/versions', self::TEST_SUBJECT_NAME), ['schema' => '[]'])
            ->willReturn([]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $api->registerNewSchemaVersion(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
    }

    public function testCheckSchemaCompatibilityForVersionTrue(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('compatibility/subjects/%s/versions/%s', self::TEST_SUBJECT_NAME, self::TEST_VERSION),
                ['schema' => '[]']
            )
            ->willReturn(['is_compatible' => true]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA, self::TEST_VERSION);
        $this->assertTrue($result);
    }

    public function testCheckSchemaCompatibilityForVersionFalse(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('compatibility/subjects/%s/versions/%s', self::TEST_SUBJECT_NAME, self::TEST_VERSION),
                ['schema' => '[]']
            )
            ->willReturn(['is_compatible' => false]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA, self::TEST_VERSION);
        $this->assertFalse($result);
    }

    public function testCheckSchemaCompatibilityForVersionNotFound(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('compatibility/subjects/%s/versions/%s', self::TEST_SUBJECT_NAME, self::TEST_VERSION),
                ['schema' => '[]']
            )
            ->willThrowException(new SubjectNotFoundException());

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA, self::TEST_VERSION);
        $this->assertTrue($result);
    }

    public function testGetSubjectCompatibilityLevel(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'GET',
                sprintf('config/%s', self::TEST_SUBJECT_NAME)
            )
            ->willReturn(['compatibilityLevel' => KafkaSchemaRegistryApiClientInterface::LEVEL_FULL]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getSubjectCompatibilityLevel(self::TEST_SUBJECT_NAME);
        $this->assertSame(KafkaSchemaRegistryApiClientInterface::LEVEL_FULL, $result);
    }

    public function testGetDefaultCompatibiltyLeveWhenGetSubjectCompatibilityLevelThrowsException(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->at(0))
            ->method('call')
            ->with('GET', sprintf('config/%s', self::TEST_SUBJECT_NAME))
            ->willThrowException(new SubjectNotFoundException());

        $httpClientMock
            ->expects($this->at(1))
            ->method('call')
            ->willReturn(['compatibilityLevel' => KafkaSchemaRegistryApiClientInterface::LEVEL_FULL]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getSubjectCompatibilityLevel(self::TEST_SUBJECT_NAME);
        $this->assertSame(KafkaSchemaRegistryApiClientInterface::LEVEL_FULL, $result);
    }

    public function testSetSubjectCompatibilityLevel(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'PUT',
                sprintf('config/%s', self::TEST_SUBJECT_NAME),
                ['compatibility' => KafkaSchemaRegistryApiClientInterface::LEVEL_FULL]
            );

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->setSubjectCompatibilityLevel(
            self::TEST_SUBJECT_NAME,
            KafkaSchemaRegistryApiClientInterface::LEVEL_FULL
        );
        
        $this->assertTrue($result);
    }

    public function testGetDefaultCompatibilityLeve(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('config'))
            ->willReturn(['compatibilityLevel' => KafkaSchemaRegistryApiClientInterface::LEVEL_FULL]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getDefaultCompatibilityLevel();
        $this->assertSame(KafkaSchemaRegistryApiClientInterface::LEVEL_FULL, $result);
    }

    public function testSetDefaultCompatibilityLeve(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('PUT', 'config', ['compatibility' => KafkaSchemaRegistryApiClientInterface::LEVEL_FULL]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->setDefaultCompatibilityLevel();
        $this->assertTrue($result);
    }

    public function testGetVersionForSchema(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('subjects/%s', self::TEST_SUBJECT_NAME),
                ['schema' => '[]']
            )
            ->willReturn(['version' => self::TEST_VERSION]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getVersionForSchema(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
        $this->assertSame((string) self::TEST_VERSION, $result);
    }

    public function testGetVersionForSchemaThrowsSubjectNotFoundExceptionResultsAsNull(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('subjects/%s', self::TEST_SUBJECT_NAME),
                ['schema' => '[]']
            )
            ->willThrowException(new SubjectNotFoundException());

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getVersionForSchema(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
        $this->assertNull($result);
    }

    public function testGetVersionForSchemaThrowsSchematNotFoundExceptionResultsAsNull(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('subjects/%s', self::TEST_SUBJECT_NAME),
                ['schema' => '[]']
            )
            ->willThrowException(new SchemaNotFoundException());

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getVersionForSchema(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
        $this->assertNull($result);
    }

    public function testSchemaExistsTrue(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('subjects/%s', self::TEST_SUBJECT_NAME),
                ['schema' => '[]']
            )
            ->willReturn(['version' => self::TEST_VERSION]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->isSchemaAlreadyRegistered(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
        $this->assertTrue($result);
    }

    public function testSchemaExistsFalse(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('subjects/%s', self::TEST_SUBJECT_NAME),
                ['schema' => '[]']
            )
            ->willThrowException(new SubjectNotFoundException());

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->isSchemaAlreadyRegistered(self::TEST_SUBJECT_NAME, self::TEST_SCHEMA);
        $this->assertFalse($result);
    }

    public function testDeleteSubject(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('DELETE', sprintf('subjects/%s', self::TEST_SUBJECT_NAME))
            ->willReturn([1,2,3,4]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->deleteSubject(self::TEST_SUBJECT_NAME);

        $this->assertSame([1,2,3,4], $result);
    }

    public function testGetLatestSubjectVersion(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('subjects/%s/versions', self::TEST_SUBJECT_NAME))
            ->willReturn([1,2,3,4,5,6]);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->getLatestSubjectVersion(self::TEST_SUBJECT_NAME);
        $this->assertSame('6', $result);
    }

    public function testImportModeFail(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'PUT',
                'mode/',
                ['mode' => 'ABC']
            )
            ->willThrowException(new ImportException());

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);

        $this->expectException(ImportException::class);
        $api->setImportMode('ABC');
    }

    public function testImportModeSuccess(): void
    {
        $httpClientMock = $this->getHttpClientMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'PUT',
                'mode/',
                ['mode' => 'ABC']
            )->willReturn(['mode' => 'ABC']);

        $api = new KafkaSchemaRegistryApiClient($httpClientMock);
        $result = $api->setImportMode('ABC');
        self::assertTrue(true, $result);
    }
}