<?php

namespace SenseiTarzan\HackBlockAndItemRegistry;

use pocketmine\item\{Item, ItemBlock};

use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\data\bedrock\item\SavedItemData as Data;

use {ReflectionProperty, ReflectionException, InvalidArgumentException, Closure};

class HackRegisterItem
{

    /**
     * @param Item|ItemBlock $item
     * @param string|int $id
     * @param Closure(never) : Data $closure
     * @param bool $isSerializer (true = serializer, false = deserializer)
     * @return void
     * @throws ReflectionException
     * @internal This method is only for internal use.
     */
    private static function registerItem(Item|ItemBlock $item, string|int $id, Closure $closure, bool $isSerializer = true): void
    {
        $instance = $isSerializer ? GlobalItemDataHandlers::getSerializer() : GlobalItemDataHandlers::getDeserializer();

        try {

            $item instanceof ItemBlock ?
                $instance->mapBlock($item, $closure) :
                $instance->map($item, $closure);

        } catch (InvalidArgumentException) {

            ($property = new ReflectionProperty($instance,
                ($isSerializer ? ($item instanceof ItemBlock ? "blockI" : "i") . "temSerializers" : "deserializers")
            ))->setAccessible(true);
            $value = $property->getValue($instance);
            $value[$id] = $closure;
            $property->setValue($instance, $value);

        }
    }

    public static function registerSerializerAndDeserializerItem(Item|ItemBlock $item, string $id, Closure $serializer, Closure $deserializer): void
    {
        foreach ([[true, $serializer], [false, $deserializer]] as [$isSerializer, $closure])
            self::registerItem($item, $isSerializer ? $isSerializer ? $item->getTypeId() : $id, $closure, $isSerializer);
    }
}
