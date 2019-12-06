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

    public const SETTINGS_KEY = 'kafka_schema_registry';
    public const SETTING_KEY_USERNAME = 'username';
    public const SETTING_PASSWORD = 'password';
    public const SETTING_BASE_URL = 'base_url';
    public const CLIENT = '_client';
    public const REQUEST_FACTORY = '_request_factory';
    public const HTTP_CLIENT = '_http_client';
    public const API_CLIENT = '_api_client';
    public const ERROR_HANDLER = '_error_handler';


    public function register(Container $container)
    {
        $this->checkRequiredOffsets($container);

        if (false === isset($container[self::REQUEST_FACTORY])) {
            $container[self::REQUEST_FACTORY] = new Psr17Factory();
        }

        if (false === isset($container[self::CLIENT])) {
            $container[self::CLIENT] = new Curl(
                $container[self::REQUEST_FACTORY]
            );
        }

        if (false === isset($container[self::ERROR_HANDLER])) {
            $container[self::ERROR_HANDLER] = new ErrorHandler();
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
                    $container[self::SETTINGS_KEY][self::SETTING_BASE_URL],
                    $container[self::SETTINGS_KEY][self::SETTING_KEY_USERNAME] ?? null,
                    $container[self::SETTINGS_KEY][self::SETTING_PASSWORD] ?? null
                );
            };
        }

        if (false === isset($container[self::API_CLIENT])) {
            $container[self::API_CLIENT] = static function (Container $container) {
                /** @var HttpClient $client */
                $client = $container[self::HTTP_CLIENT];

                return new KafkaSchemaRegistryApiApiClient($client);
            };
        }
    }

    private function checkRequiredOffsets(Container $container)
    {

        if (false === isset($container[self::SETTINGS_KEY][self::SETTING_BASE_URL])) {
            throw new LogicException(
                sprintf(
                    'Missing schema registry URL, please set it under "%s" container offset',
                    self::SETTING_BASE_URL
                )
            );
        }
    }
}
