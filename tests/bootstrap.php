<?php

declare(strict_types=1);

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->setPsr4('Jobcloud\\Kafka\\SchemaRegistryClient\\Tests\\', __DIR__);

echo sprintf('PHP version: %s', PHP_VERSION) . PHP_EOL;
