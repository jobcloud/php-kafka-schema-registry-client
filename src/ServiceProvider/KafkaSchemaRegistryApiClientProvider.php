<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider;

use Buzz\Client\Curl;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandler;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class KafkaSchemaRegistryApiClientProvider implements ServiceProviderInterface
{

    public const CONTAINER_KEY = 'kafka.schema.registry';

    public const SETTING_KEY_USERNAME = 'username';
    public const SETTING_KEY_PASSWORD = 'password';
    public const SETTING_KEY_BASE_URL = 'base.url';

    public const CLIENT = 'kafka.schema.registry.client';
    public const REQUEST_FACTORY = 'kafka.schema.registry.request.factory';
    public const HTTP_CLIENT = 'kafka.schema.registry.client.http';
    public const API_CLIENT = 'kafka.schema.registry.client.api';
    public const ERROR_HANDLER = 'kafka.schema.registry.error.handler';

    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        $this->checkRequiredOffsets($container);

        if (false === isset($container[self::REQUEST_FACTORY])) {
            $container[self::REQUEST_FACTORY] = static function (Container $container) {
                return new Psr17Factory();
            };
        }

        if (false === isset($container[self::CLIENT])) {
            $container[self::CLIENT] = static function (Container $container) {
                return new Curl($container[self::REQUEST_FACTORY]);
            };
        }

        if (false === isset($container[self::ERROR_HANDLER])) {
            $container[self::ERROR_HANDLER] = static function () {
                return new ErrorHandler();
            };
        }

        if (false === isset($container[self::HTTP_CLIENT])) {
            $container[self::HTTP_CLIENT] = static function (Container $container) {
                /** @var ClientInterface $client */
                $client = $container[self::CLIENT];

                /** @var RequestFactoryInterface $psr17factory */
                $requestFactory = $container[self::REQUEST_FACTORY];

                return new HttpClient(
                    $client,
                    $requestFactory,
                    $container[self::ERROR_HANDLER],
                    $container[self::CONTAINER_KEY][self::SETTING_KEY_BASE_URL],
                    $container[self::CONTAINER_KEY][self::SETTING_KEY_USERNAME] ?? null,
                    $container[self::CONTAINER_KEY][self::SETTING_KEY_PASSWORD] ?? null
                );
            };
        }

        if (false === isset($container[self::API_CLIENT])) {
            $container[self::API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::HTTP_CLIENT];

                return new KafkaSchemaRegistryApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_KEY_BASE_URL])) {
            throw new LogicException(
                sprintf(
                    'Missing schema registry URL, please set it under "%s" container offset',
                    self::SETTING_KEY_BASE_URL
                )
            );
        }
    }
}
