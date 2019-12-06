<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Tests;

use Jobcloud\KafkaSchemaRegistryClient\Exception\SubjectNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\HttpClient;
use Jobcloud\KafkaSchemaRegistryClient\KafkaSchemaRegistryApiApiClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class KafkaSchemaRegistryApiApiClientTest extends TestCase
{
    public function testGetSubjects(): void
    {
        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock->expects($this->once())->method('call')->with('GET', 'subjects');

        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $api->getSubjects();
    }

    public function testGetAllSubjectVersions(): void
    {
        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $subjectName = 'some-subject';

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('/subjects/%s/versions', $subjectName));

        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $api->getAllSubjectVersions($subjectName);
    }

    public function testGetSchemaByVersion(): void
    {
        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $subjectName = 'some-subject';
        $version = '3';

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('/subjects/%s/versions/%s', $subjectName, $version))
            ->willReturn(['schema' => '{}']);

        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $result = $api->getSchemaByVersion($subjectName, $version);

        $this->assertSame('{}', $result);
    }

    public function testDeleteSchemaVersion(): void
    {
        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $subjectName = 'some-subject';
        $version = '3';

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('DELETE', sprintf('/subjects/%s/versions/%s', $subjectName, $version))
            ->willReturn(['schema' => '{}']);

        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $result = $api->deleteSchemaVersion($subjectName, $version);

        $this->assertTrue($result);
    }

    public function testGetSchemaById(): void
    {
        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('GET', sprintf('/schemas/ids/%s', 1))
            ->willReturn(['schema' => '{}']);

        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $api->getSchemaById(1);
    }

    public function testRegisterNewSchemaVersion(): void
    {
        $subjectName = 'some-subject';
        $schema = '{}';

        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with('POST', sprintf('/subjects/%s/versions', $subjectName), ['schema' => '[]'])
            ->willReturn([]);


        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $api->registerNewSchemaVersion($subjectName, $schema);
    }

    public function testCheckSchemaCompatibilityForVersionTrue(): void
    {
        $subjectName = 'some-subject';
        $schema = '{}';
        $version = 3;

        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('/compatibility/subjects/%s/versions/%s', $subjectName, $version),
                ['schema' => '[]']
            )
            ->willReturn(['is_compatible' => true]);


        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion($subjectName, $schema, $version);
        $this->assertTrue($result);
    }

    public function testCheckSchemaCompatibilityForVersionFalse(): void
    {
        $subjectName = 'some-subject';
        $schema = '{}';
        $version = 3;

        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('/compatibility/subjects/%s/versions/%s', $subjectName, $version),
                ['schema' => '[]']
            )
            ->willReturn(['is_compatible' => false]);


        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion($subjectName, $schema, $version);
        $this->assertFalse($result);
    }

    public function testCheckSchemaCompatibilityForVersionNotFound(): void
    {
        $subjectName = 'some-subject';
        $schema = '{}';
        $version = 3;

        /** @var MockObject|HttpClient $httpClientMock */
        $httpClientMock = $this
            ->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['call'])
            ->getMock();

        $httpClientMock
            ->expects($this->once())
            ->method('call')
            ->with(
                'POST',
                sprintf('/compatibility/subjects/%s/versions/%s', $subjectName, $version),
                ['schema' => '[]']
            )
            ->willThrowException(new SubjectNotFoundException());


        $api = new KafkaSchemaRegistryApiApiClient($httpClientMock);
        $result = $api->checkSchemaCompatibilityForVersion($subjectName, $schema, $version);
        $this->assertTrue($result);
    }

}