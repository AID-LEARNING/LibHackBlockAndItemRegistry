<?php

namespace SenseiTarzan\HackBlockAndItemRegistry;

use Closure;
use InvalidArgumentException;
use pocketmine\data\bedrock\item\SavedItemData as Data;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionException;

class HackRegisterItem
{

    /**
     * @param Item $item
     * @param Closure(never) : Data $serializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerSerializerItem(Item $item, Closure $serializer): void
    {
        $instance = GlobalItemDataHandlers::getSerializer();
        try {
            $instance->map($item, $serializer);
        } catch (InvalidArgumentException) {
            $serializerProperty = new \ReflectionProperty($instance, "itemSerializers");
            $serializerProperty->setAccessible(true);
            $value = $serializerProperty->getValue($instance);
            if (isset($value[$item->getTypeId()])) {
                $value[$item->getTypeId()] = [];
            }
            $value[$item->getTypeId()][get_class($item)] = $serializer;
            $serializerProperty->setValue($instance, $value);
        }

    }

    /**
     * @param ItemBlock $itemBlock
     * @param Closure(never) : Data $serializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerSerializerItemBlock(ItemBlock $itemBlock, Closure $serializer): void
    {
        $instance = GlobalItemDataHandlers::getSerializer();
        try {
            $instance->mapBlock($itemBlock, $serializer);
        } catch (InvalidArgumentException) {
            $serializerProperty = new \ReflectionProperty($instance, "blockItemSerializers");
            $serializerProperty->setAccessible(true);
            $value = $serializerProperty->getValue($instance);
            if (isset($value[$itemBlock->getTypeId()])) {
                $value[$itemBlock->getTypeId()] = [];
            }
            $value[$itemBlock->getTypeId()][get_class($itemBlock)] = $serializer;
            $serializerProperty->setValue($instance, $value);
        }
    }

    /**
     * @param string $id
     * @param Closure(Data) : Item $deserializer
     * @return void
     * @throws ReflectionException
     */
    public static function registerDeserializerItem(string $id, Closure $deserializer): void
    {
        $instance = GlobalItemDataHandlers::getDeserializer();
        try {
            $instance->map($id, $deserializer);
        } catch (InvalidArgumentException) {
            $deserializerProperty = new \ReflectionProperty($instance, "itemDeserializers");
            $deserializerProperty->setAccessible(true);
            $value = $deserializerProperty->getValue($instance);
            $value[$id] = $deserializer;
            $deserializerProperty->setValue($instance, $value);
        }
    }

    public static function reisterSerializerAndDeserializerItem(Item $item, string $id, Closure $serializer, Closure $deserializer): void
    {
        self::registerSerializerItem($item, $serializer);
        self::registerDeserializerItem($id, $deserializer);
    }

    public static function reisterSerializerAndDeserializerItemBlock(ItemBlock $itemBlock, string $id, Closure $serializer, Closure $deserializer): void
    {
        self::registerSerializerItemBlock($itemBlock, $serializer);
        self::registerDeserializerItem($id, $deserializer);
    }

}