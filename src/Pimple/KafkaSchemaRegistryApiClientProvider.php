<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Pimple;

use Buzz\Client\Curl;
use Jobcloud\KafkaSchemaRegistryClient\ErrorHandler;
use Jobcloud\KafkaSchemaRegistryClient\HttpClient;
use Jobcloud\KafkaSchemaRegistryClient\KafkaSchemaRegistryApiApiClient;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class KafkaSchemaRegistryApiClientProvider implements ServiceProviderInterface
{

    public const CONTAINER_KEY = 'kafka_schema_registry';
    public const SETTING_KEY_USERNAME = 'username';
    public const PASSWORD = 'password';
    public const BASE_URL = 'base_url';
    public const CLIENT = 'client';
    public const REQUEST_FACTORY = 'request_factory';
    public const HTTP_CLIENT = 'http_client';
    public const API_CLIENT = 'api_client';
    public const ERROR_HANDLER = 'error_handler';


    public function register(Container $container)
    {
        $this->checkRequiredOffsets($container);

        if (false === isset($container[self::CONTAINER_KEY][self::REQUEST_FACTORY])) {
            $container[self::CONTAINER_KEY][self::REQUEST_FACTORY] = new Psr17Factory();
        }

        if (false === isset($container[self::CONTAINER_KEY][self::CLIENT])) {
            $container[self::CONTAINER_KEY][self::CLIENT] = new Curl(
                $container[self::CONTAINER_KEY][self::REQUEST_FACTORY]
            );
        }

        if (false === isset($container[self::CONTAINER_KEY][self::ERROR_HANDLER])) {
            $container[self::CONTAINER_KEY][self::ERROR_HANDLER] = new ErrorHandler();
        }

        if (false === isset($container[self::CONTAINER_KEY][self::HTTP_CLIENT])) {
            $container[self::CONTAINER_KEY][self::HTTP_CLIENT] = static function (Container $container) {
                /** @var ClientInterface $client */
                $client = $container[self::CONTAINER_KEY][self::CLIENT];

                /** @var RequestFactoryInterface $psr17factory */
                $requestFactory = $container[self::CONTAINER_KEY][self::REQUEST_FACTORY];

                return new HttpClient(
                    $client,
                    $requestFactory,
                    $container[self::CONTAINER_KEY][self::ERROR_HANDLER],
                    $container[self::CONTAINER_KEY][self::BASE_URL],
                    $container[self::CONTAINER_KEY][self::SETTING_KEY_USERNAME] ?? null,
                    $container[self::CONTAINER_KEY][self::PASSWORD] ?? null
                );
            };
        }

        if (false === isset($container[self::CONTAINER_KEY][self::API_CLIENT])) {
            $container[self::CONTAINER_KEY][self::API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::CONTAINER_KEY][self::HTTP_CLIENT];

                return new KafkaSchemaRegistryApiApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === isset($container[self::CONTAINER_KEY][self::BASE_URL])) {
            throw new LogicException(
                sprintf('Missing schema registry URL, please set it under "%s" container offset', self::BASE_URL)
            );
        }
    }
}
