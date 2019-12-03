<?php

namespace Jobcloud\KafkaSchemaRegistryClient\Pimple;

use Buzz\Client\Curl;
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

    public const USERNAME = 'kafka_schema_registry_username';
    public const PASSWORD = 'kafka_schema_registry_password';
    public const BASE_URL = 'kafka_schema_registry_base_url';
    public const CLIENT = 'kafka_schema_registry_client';
    public const REQUEST_FACTORY = 'kafka_schema_registry_request_factory';
    public const HTTP_CLIENT = 'kafka_schema_registry_http_client';
    public const API_CLIENT = 'kafka_schema_registry_api_client';


    public function register(Container $container)
    {
        $this->checkRequiredOffsets($container);

        if (false === $container->offsetExists(self::REQUEST_FACTORY)) {
            $container[self::REQUEST_FACTORY] = new Psr17Factory();
        }

        if (false === $container->offsetExists(self::CLIENT)) {
            $container[self::CLIENT] = new Curl($container[self::REQUEST_FACTORY]);
        }

        if (false === $container->offsetExists(self::HTTP_CLIENT)) {
            $container[self::HTTP_CLIENT] = static function (Container $container) {
                /** @var ClientInterface $client */
                $client = $container[self::CLIENT];

                /** @var RequestFactoryInterface $psr17factory */
                $requestFactory = $container[self::REQUEST_FACTORY];

                return new HttpClient(
                    $client,
                    $requestFactory,
                    $container[self::BASE_URL],
                    $container[self::USERNAME] ?? null,
                    $container[self::PASSWORD] ?? null
                );
            };
        }

        if (false === $container->offsetExists(self::API_CLIENT)) {
            $container[self::API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::HTTP_CLIENT];

                return new KafkaSchemaRegistryApiApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === $container->offsetExists(self::BASE_URL)) {
            throw new LogicException(
                sprintf('Missing schema registry URL, please set it under "%s" container offset', self::BASE_URL)
            );
        }
    }
}
