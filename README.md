[![CircleCI](https://circleci.com/gh/jobcloud/php-kafka-schema-registry-client/tree/master.svg?style=svg)](https://circleci.com/gh/jobcloud/php-kafka-schema-registry-client/tree/master)

# Kafka Schema Registry API Client

## What is it?
An API Client written in PHP to communicate with Kafka Schema Registry.

## What it can do?
Currently it supports:

* Get list of subjects
* Get list of schema versions for a subject
* Delete Subject
* Delete Schema version
* Get Subject's Schema by version
* Get Schema by ID 
* Register new Schema Version for a subject
* Check Schema compatibility for schema version that exist for subject
* Get Default Compatibility level
* Set Default Compatibility level
* Get Compatibility level of subject
* Set Compatibility level for subject
* Get Version by providing schema for a subject
* Get Subject's latest schema version
* Setting the registry mode

## How to use it?
### Installation
```bash
composer require jobcloud/php-kafka-schema-registry-client
```

### Code example

```php
<?php

use Buzz\Client\Curl;
use Jobcloud\Kafka\SchemaRegistryClient\ErrorHandler;
use Jobcloud\Kafka\SchemaRegistryClient\HttpClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Nyholm\Psr7\Factory\Psr17Factory;

require 'vendor/autoload.php';

$psr17Factory = new Psr17Factory();
$client = new Curl($psr17Factory);
$username = 'USERNAME';
$password = 'PASSWORD';

$registryClient = new HttpClient(
    $client,
    $psr17Factory,
    new ErrorHandler(),
    'http://your-registry-schema-server-url:9081',
    $username ?? null,
    $password ?? null
);

$schema = '{"type":"record","name":"something","namespace":"whatever.you.want","fields":[{"name":"id","type":"string"}]}';
$registryClientApi = new KafkaSchemaRegistryApiClient($registryClient);
$subjectName = 'some.subject.name';

$results = $registryClientApi->getVersionForSchema($subjectName, $schema);
```

If you are using Pimple Container in you App, you can use Service Provider:
```php

use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use Pimple\Container;

$container = new Container();

$container['kafka.schema.registry'] = [
    'base.url' => 'http://your-registry-schema-server-url:9081',
    'username' => 'your_username',
    'password' => 'your_password',
];

$container->register(new KafkaSchemaRegistryApiClientProvider());

$api = $container['kafka.schema.registry.client.api']);

$data = $api->getSubjects();
```

## External links?
If you want to be so kind to extend this library, make a pull request, 
and whatever functionality you want to implement, this is a API reference to follow: 
https://docs.confluent.io/current/schema-registry/develop/api.html
