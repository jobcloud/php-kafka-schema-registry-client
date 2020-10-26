<?php

namespace Jobcloud\Kafka\SchemaRegistryClient;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\BackendDatastoreException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\ImportException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\ClientException;
use Jobcloud\Kafka\SchemaRegistryClient\Exception\CompatibilityException;
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
use Psr\Http\Message\ResponseInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param ResponseInterface $response
     * @param string|null       $uri
     * @return void
     * @throws BackendDatastoreException
     * @throws ClientException
     * @throws CompatibilityException
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
     * @throws ImportException
     */
    public function handleError(ResponseInterface $response, string $uri = null): void
    {
        $responseContent = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (false === isset($responseContent['error_code'])) {
            return;
        }

        $code = $responseContent['error_code'];
        $message = $responseContent['message'] ?? '';

        if (null !== $uri) {
            $message .= sprintf(' (%s)', $uri);
        }

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
            case 42205:
                throw new ImportException($message);
            case 40401:
                throw new SubjectNotFoundException($message);
            case 40402:
                throw new VersionNotFoundException($message);
            case 40403:
                throw new SchemaNotFoundException($message);
            case 409:
                throw new IncompatibileAvroSchemaException($message);
            case 422:
                throw new UnprocessableEntityException($message);
            case 404:
                throw new PathNotFoundException($message);
            case 401:
                throw new UnauthorizedException($message);
            default:
                throw new ClientException($message, $code);
        }
    }
}
