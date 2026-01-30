<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use ReflectionClass;
use ReflectionException;

trait ReflectionAccessTrait
{
    /**
     * @throws ReflectionException
     */
    final public function setProperty(object $object, string $propertyName, mixed $newProperty): void
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);

        $property->setValue($object, $newProperty);
    }

    /**
     * @throws ReflectionException
     */
    final public function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);

        return $property->getValue($object);
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws ReflectionException
     */
    final public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }
}
