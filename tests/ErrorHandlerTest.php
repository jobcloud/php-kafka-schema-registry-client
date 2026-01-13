<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use Buzz\Exception\ClientException;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandler;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\BackendDatastoreException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\CompatibilityException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\ImportException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\IncompatibileAvroSchemaException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\InvalidAvroSchemaException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\InvalidVersionException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\OperationTimeoutException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\PathNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\RequestForwardException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\SubjectNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\UnauthorizedException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\UnprocessableEntityException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\VersionNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(ErrorHandler::class)]
#[AllowMockObjectsWithoutExpectations]
class ErrorHandlerTest extends TestCase
{
    private const string TEST_MESSAGE = 'Test Message';

    private function makeResponseInterfaceMock(?int $code = null, ?string $message = null): MockObject
    {
        $streamMock = $this->createMock(StreamInterface::class);

        $streamMock
            ->method('__toString')
            ->willReturn(json_encode([
                'error_code' => $code,
                'message' => $message,
            ]));

        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->method('getBody')->willReturn($streamMock);

        return $responseMock;
    }

    public static function exceptionTestDataProvider(): array
    {
        return [
            [50001, BackendDatastoreException::class],
            [50002, OperationTimeoutException::class],
            [50003, RequestForwardException::class],
            [42201, InvalidAvroSchemaException::class],
            [42202, InvalidVersionException::class],
            [42203, CompatibilityException::class],
            [42205, ImportException::class],
            [40401, SubjectNotFoundException::class],
            [40402, VersionNotFoundException::class],
            [40403, SchemaNotFoundException::class],
            [40403, SchemaNotFoundException::class],
            [409, IncompatibileAvroSchemaException::class],
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
     * @throws BackendDatastoreException
     * @throws ClientException
     * @throws CompatibilityException
     * @throws ImportException
     * @throws IncompatibileAvroSchemaException
     * @throws InvalidAvroSchemaException
     * @throws InvalidVersionException
     * @throws OperationTimeoutException
     * @throws PathNotFoundException
     * @throws RequestForwardException
     * @throws SchemaNotFoundException
     * @throws SubjectNotFoundException
     * @throws UnauthorizedException
     * @throws UnprocessableEntityException
     * @throws VersionNotFoundException
     */
    #[DataProvider('exceptionTestDataProvider')]
    public function testExceptionThrow(?int $code, string $expectedException): void
    {
        /** @var ResponseInterface|MockObject $responseMock */
        $responseMock = $this->makeResponseInterfaceMock($code, self::TEST_MESSAGE);

        $errorHandler = new ErrorHandler();

        $this->expectException($expectedException);
        $this->expectExceptionMessage(self::TEST_MESSAGE);

        $errorHandler->handleError($responseMock);
    }

    public function testExceptionThrowWithUri(): void
    {
        /** @var ResponseInterface|MockObject $responseMock */
        $responseMock = $this->makeResponseInterfaceMock(50001, self::TEST_MESSAGE);

        $errorHandler = new ErrorHandler();

        $this->expectException(BackendDatastoreException::class);
        $this->expectExceptionMessage(self::TEST_MESSAGE . sprintf(' (%s)', 'http://test.com'));

        $errorHandler->handleError($responseMock, 'http://test.com');
    }

    public function testExceptionThrowWithRequest(): void
    {
        /** @var ResponseInterface|MockObject $responseMock */
        $responseMock = $this->makeResponseInterfaceMock(50001, self::TEST_MESSAGE);
        $requestMock = $this->createMock(RequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects(self::once())->method('getContents')->willReturn('test body');
        $requestMock->expects(self::once())->method('getBody')->willReturn($streamMock);

        $errorHandler = new ErrorHandler();

        $this->expectException(BackendDatastoreException::class);
        $this->expectExceptionMessage(
            self::TEST_MESSAGE . sprintf(' (%s) with request body: %s', 'http://test.com', 'test body')
        );

        $errorHandler->handleError($responseMock, 'http://test.com', $requestMock);
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
