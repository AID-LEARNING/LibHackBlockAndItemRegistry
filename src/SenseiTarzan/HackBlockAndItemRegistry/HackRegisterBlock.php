<?php

namespace SenseiTarzan\HackBlockAndItemRegistry;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

class HackRegisterBlock
{
    /**
     * @param Block $block
     * @param \Closure $serializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerBlockSerializer(Block $block, \Closure $serializer): void
    {
        $instance = GlobalBlockStateHandlers::getSerializer();
        try {
            $instance->map($block, $serializer);
        } catch (InvalidArgumentException) {
            $serializerProperty = new ReflectionProperty($instance, "serializers");
            $serializerProperty->setAccessible(true);
            $value = $serializerProperty->getValue($instance);
            if (isset($value[$block->getTypeId()])) {
                $value[$block->getTypeId()] = [];
            }
            $value[$block->getTypeId()][get_class($block)] = $serializer;
            $serializerProperty->setValue($instance, $value);
        }
    }

    /**
     * @param Block $block
     * @param \Closure $deserializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerBlockDeserializer(Block $block, \Closure $deserializer): void
    {
        $instance = GlobalBlockStateHandlers::getDeserializer();
        try {
            $instance->map($block, $deserializer);
        } catch (InvalidArgumentException) {
            $deserializerProperty = new ReflectionProperty($instance, "deserializeFuncs");
            $deserializerProperty->setAccessible(true);
            $value = $deserializerProperty->getValue($instance);
            if (isset($value[$block->getTypeId()])) {
                $value[$block->getTypeId()] = [];
            }
            $value[$block->getTypeId()] = $deserializer;
            $deserializerProperty->setValue($instance, $value);
        }
    }

    /**
     * @param Block $block
     * @param \Closure $serializer
     * @param \Closure $deserializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerBlockAndSerializerAndDeserializer(Block $block, \Closure $serializer, \Closure $deserializer): void
    {
        self::registerRuntimeBlockStateRegistry($block);
        self::registerBlockSerializer($block, $serializer);
        self::registerBlockDeserializer($block, $deserializer);
    }

    /**
     * @param Block $block
     * @return void
     * @throws ReflectionException
     */
    public static function registerRuntimeBlockStateRegistry(Block $block): void
    {
        $instance = RuntimeBlockStateRegistry::getInstance();
        try {
            $instance->register($block);
        } catch (InvalidArgumentException) {
            $typeIndexProperty = new ReflectionProperty($instance, "typeIndex");
            $typeIndexProperty->setAccessible(true);
            $value = $typeIndexProperty->getValue($instance);
            $value[$block->getTypeId()] = $block;
            $typeIndexProperty->setValue($instance, $value);

            $fillStaticArraysMethod = new ReflectionMethod($instance, "fillStaticArrays");
            $fillStaticArraysMethod->setAccessible(true);
            foreach ($block->generateStatePermutations() as $v) {
                $fillStaticArraysMethod->invoke($instance, $v->getStateId(), $v);
            }
        }
    }
}