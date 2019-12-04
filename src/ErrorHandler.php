<?php


namespace Jobcloud\KafkaSchemaRegistryClient;

use Jobcloud\KafkaSchemaRegistryClient\Exceptions\BackendDatastoreException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\ClientException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\CompatibilityException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\InvalidAvroSchemaException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\InvalidVersionException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\OperationTimeoutException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\PathNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\RequestForwardException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\SubjectNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\UnauthorizedException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\UnprocessableEntityException;
use Jobcloud\KafkaSchemaRegistryClient\Exceptions\VersionNotFoundException;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\ErrorHandlerInterface;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param string $errorCode
     * @param string $errorMessage
     * @return void
     * @throws BackendDatastoreException
     * @throws ClientException
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
     */
    public function handleResponseData(string $errorCode, string $errorMessage): void
    {

        switch ($errorCode) {

            case 50001:
                throw new BackendDatastoreException($errorMessage);
            case 50002:
                throw new OperationTimeoutException($errorMessage);
            case 50003:
                throw new RequestForwardException($errorMessage);
            case 42201:
                throw new InvalidAvroSchemaException($errorMessage);
            case 42202:
                throw new InvalidVersionException($errorMessage);
            case 42203:
                throw new CompatibilityException($errorMessage);
            case 40401:
                throw new SubjectNotFoundException($errorMessage);
            case 40402:
                throw new VersionNotFoundException($errorMessage);
            case 409:
                throw new IncompatibleReturnValueException($errorMessage);
            case 422:
                throw new UnprocessableEntityException($errorMessage);
            case 404:
                throw new PathNotFoundException($errorMessage);
            case 401:
                throw new UnauthorizedException($errorMessage);
            default:
                throw new ClientException($errorMessage);
        }
    }
}