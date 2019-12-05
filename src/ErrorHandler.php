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
use Psr\Http\Message\ResponseInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param ResponseInterface $response
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
    public function handleError(ResponseInterface $response): void
    {

        $responseContent = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (false === isset($responseContent['error_code'])) {
            return;
        }

        $code = $responseContent['error_code'];
        $message = $responseContent['message'] ?? '';

        switch ($code) {
            case 50001:
                throw new BackendDatastoreException($message);
            case 50002:
                throw new OperationTimeoutException($message);
            case 50003:
                throw new RequestForwardException($message);
            case 42201:
                throw new InvalidAvroSchemaException($message);
            case 42202:
                throw new InvalidVersionException($message);
            case 42203:
                throw new CompatibilityException($message);
            case 40401:
                throw new SubjectNotFoundException($message);
            case 40402:
                throw new VersionNotFoundException($message);
            case 409:
                throw new IncompatibleReturnValueException($message);
            case 422:
                throw new UnprocessableEntityException($message);
            case 404:
                throw new PathNotFoundException($message);
            case 401:
                throw new UnauthorizedException($message);
            default:
                throw new ClientException($message);
        }
    }
}
