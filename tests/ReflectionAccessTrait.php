<?php

namespace Jobcloud\Kafka\SchemaRegistryClient\Tests;

use ReflectionClass;
use ReflectionException;

trait ReflectionAccessTrait
{
    /**
     * Set private/protected property.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $propertyName Property which will be set
     * @param mixed $newProperty New property value
     * @throws ReflectionException
     */
    final public function setProperty(object $object, string $propertyName, mixed $newProperty): void
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($object, $newProperty);
    }

    /**
     * Set private/protected property.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $propertyName Property which value will be returned
     * @throws ReflectionException
     */
    final public function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @throws ReflectionException
     */
    final public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
