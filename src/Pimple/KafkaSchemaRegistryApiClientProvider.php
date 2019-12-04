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

    public const ROOT = 'kafka_schema_registry';
    public const USERNAME = 'username';
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

        if (false === isset($container[self::ROOT][self::REQUEST_FACTORY])) {
            $container[self::ROOT][self::REQUEST_FACTORY] = new Psr17Factory();
        }

        if (false === isset($container[self::ROOT][self::CLIENT])) {
            $container[self::ROOT][self::CLIENT] = new Curl($container[self::ROOT][self::REQUEST_FACTORY]);
        }

        if (false === isset($container[self::ROOT][self::ERROR_HANDLER])) {
            $container[self::ROOT][self::ERROR_HANDLER] = new ErrorHandler();
        }

        if (false === isset($container[self::ROOT][self::HTTP_CLIENT])) {
            $container[self::ROOT][self::HTTP_CLIENT] = static function (Container $container) {
                /** @var ClientInterface $client */
                $client = $container[self::ROOT][self::CLIENT];

                /** @var RequestFactoryInterface $psr17factory */
                $requestFactory = $container[self::ROOT][self::REQUEST_FACTORY];

                return new HttpClient(
                    $client,
                    $requestFactory,
                    $container[self::ROOT][self::ERROR_HANDLER],
                    $container[self::ROOT][self::BASE_URL],
                    $container[self::ROOT][self::USERNAME] ?? null,
                    $container[self::ROOT][self::PASSWORD] ?? null
                );
            };
        }

        if (false === isset($container[self::ROOT][self::API_CLIENT])) {
            $container[self::ROOT][self::API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::ROOT][self::HTTP_CLIENT];

                return new KafkaSchemaRegistryApiApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === isset($container[self::ROOT][self::BASE_URL])) {
            throw new LogicException(
                sprintf('Missing schema registry URL, please set it under "%s" container offset', self::BASE_URL)
            );
        }
    }
}
