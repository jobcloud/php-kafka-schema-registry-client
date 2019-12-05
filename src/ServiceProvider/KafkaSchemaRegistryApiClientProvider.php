<?php

namespace Jobcloud\KafkaSchemaRegistryClient\ServiceProvider;

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
    public const SETTING_PASSWORD = 'password';
    public const SETTING_BASE_URL = 'base_url';
    public const SETTING_CLIENT = 'client';
    public const SETTING_REQUEST_FACTORY = 'request_factory';
    public const SETTING_HTTP_CLIENT = 'http_client';
    public const SETTING_API_CLIENT = 'api_client';
    public const SETTING_ERROR_HANDLER = 'error_handler';


    public function register(Container $container)
    {
        $this->checkRequiredOffsets($container);

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_REQUEST_FACTORY])) {
            $container[self::CONTAINER_KEY][self::SETTING_REQUEST_FACTORY] = new Psr17Factory();
        }

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_CLIENT])) {
            $container[self::CONTAINER_KEY][self::SETTING_CLIENT] = new Curl(
                $container[self::CONTAINER_KEY][self::SETTING_REQUEST_FACTORY]
            );
        }

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_ERROR_HANDLER])) {
            $container[self::CONTAINER_KEY][self::SETTING_ERROR_HANDLER] = new ErrorHandler();
        }

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_HTTP_CLIENT])) {
            $container[self::CONTAINER_KEY][self::SETTING_HTTP_CLIENT] = static function (Container $container) {
                /** @var ClientInterface $client */
                $client = $container[self::CONTAINER_KEY][self::SETTING_CLIENT];

                /** @var RequestFactoryInterface $psr17factory */
                $requestFactory = $container[self::CONTAINER_KEY][self::SETTING_REQUEST_FACTORY];

                return new HttpClient(
                    $client,
                    $requestFactory,
                    $container[self::CONTAINER_KEY][self::SETTING_ERROR_HANDLER],
                    $container[self::CONTAINER_KEY][self::SETTING_BASE_URL],
                    $container[self::CONTAINER_KEY][self::SETTING_KEY_USERNAME] ?? null,
                    $container[self::CONTAINER_KEY][self::SETTING_PASSWORD] ?? null
                );
            };
        }

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_API_CLIENT])) {
            $container[self::CONTAINER_KEY][self::SETTING_API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::CONTAINER_KEY][self::SETTING_HTTP_CLIENT];

                return new KafkaSchemaRegistryApiApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === isset($container[self::CONTAINER_KEY][self::SETTING_BASE_URL])) {
            throw new LogicException(
                sprintf(
                    'Missing schema registry URL, please set it under "%s" container offset',
                    self::SETTING_BASE_URL
                )
            );
        }
    }
}
