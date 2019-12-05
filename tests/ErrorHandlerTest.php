<?php

use Jobcloud\KafkaSchemaRegistryClient\ErrorHandler;
use Jobcloud\KafkaSchemaRegistryClient\Exception\BackendDatastoreException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\ClientException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\CompatibilityException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\IncoompatibileAvroSchemaException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\InvalidAvroSchemaException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\InvalidVersionException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\OperationTimeoutException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\PathNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\RequestForwardException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\SubjectNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\UnauthorizedException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\UnprocessableEntityException;
use Jobcloud\KafkaSchemaRegistryClient\Exception\VersionNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ErrorHandlerTest extends TestCase
{
    private const TEST_MESSAGE = 'Test Message';

    private function makeResponseInterfaceMock(?string $code = null, ?string $message = null): MockObject
    {
        $streamMock = $this
            ->getMockBuilder(StreamInterface::class)
            ->onlyMethods(['__toString'])
            ->getMockForAbstractClass();

        $streamMock
            ->method('__toString')
            ->willReturn(json_encode([
                'error_code' => $code,
                'message' => $message,
            ]));

        $responseMock = $this
            ->getMockBuilder(ResponseInterface::class)
            ->onlyMethods(['getBody'])
            ->getMockForAbstractClass();

        $responseMock->method('getBody')->willReturn($streamMock);

        return $responseMock;
    }

    public function exceptionTestDataProvider(): array{
        return [
            [50001, BackendDatastoreException::class],
            [50002, OperationTimeoutException::class],
            [50003, RequestForwardException::class],
            [42201, InvalidAvroSchemaException::class],
            [42202, InvalidVersionException::class],
            [42203, CompatibilityException::class],
            [40401, SubjectNotFoundException::class],
            [40402, VersionNotFoundException::class],
            [409, IncoompatibileAvroSchemaException::class],
            [422, UnprocessableEntityException::class],
            [404, PathNotFoundException::class],
            [401, UnauthorizedException::class],
            [9999, ClientException::class],
            [-9999, ClientException::class],
            [0, ClientException::class],
            [PHP_INT_MAX, ClientException::class],
            [PHP_INT_MIN, ClientException::class],
        ];
    }

    /**
     * @dataProvider exceptionTestDataProvider
     *
     * @param int $code
     * @param string $expectedException
     * @throws BackendDatastoreException
     * @throws CompatibilityException
     * @throws InvalidAvroSchemaException
     * @throws InvalidVersionException
     * @throws OperationTimeoutException
     * @throws PathNotFoundException
     * @throws RequestForwardException
     * @throws SubjectNotFoundException
     * @throws UnauthorizedException
     * @throws UnprocessableEntityException
     * @throws VersionNotFoundException
     * @throws ClientException
     * @throws IncoompatibileAvroSchemaException
     */
    public function testExceptionThrow(int $code, string $expectedException): void
    {
        /** @var ResponseInterface|MockObject $responseMock */
        $responseMock = $this->makeResponseInterfaceMock($code, self::TEST_MESSAGE);

        $errorHandler = new ErrorHandler();

        $this->expectExceptionMessage($expectedException);
        $this->expectExceptionMessage(self::TEST_MESSAGE);

        $errorHandler->handleError($responseMock);
    }

    public function testNoExceptionIfNoErrorCode(): void
    {
        /** @var ResponseInterface|MockObject $responseMock */
        $responseMock = $this->makeResponseInterfaceMock();
        $errorHandler = new ErrorHandler();

        $errorHandler->handleError($responseMock);

        // It sounds weird, trust me...
        $this->assertTrue(true);
    }
}