<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\trigger\block\BlockTrigger;
use aieuo\mineflow\trigger\block\BlockTriggerForm;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\trigger\command\CommandTriggerForm;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\custom\CustomTriggerForm;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\event\EventTriggerForm;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\trigger\form\FormTriggerForm;
use aieuo\mineflow\trigger\time\TimeTrigger;
use aieuo\mineflow\trigger\time\TimeTriggerForm;

class Triggers {

    public const BLOCK = "block";
    public const COMMAND = "command";
    public const EVENT = "event";
    public const FORM = "form";
    public const TIME = "time";
    public const CUSTOM = "custom";

    /** @var TriggerForm[] */
    private static array $forms = [];

    /** @var (\Closure(Trigger $trigger): array)[] */
    private static array $serializers = [];
    /** @var (\Closure(array $data): Trigger)[] */
    private static array $deserializers = [];

    public static function init(): void {
        self::add(self::BLOCK, new BlockTriggerForm(), function (BlockTrigger $trigger) {
            return [
                "position" => $trigger->getPositionString(),
            ];
        }, function (array $data) {
            return new BlockTrigger($data["position"] ?? $data["key"]);
        });

        self::add(self::COMMAND, new CommandTriggerForm(), function (CommandTrigger $trigger) {
            return [
                "command" => $trigger->getCommand(),
                "full" => $trigger->getFullCommand(),
            ];
        }, function (array $data) {
            return new CommandTrigger($data["full"] ?? $data["subKey"]);
        });

        self::add(self::EVENT, new EventTriggerForm(), function (EventTrigger $trigger) {
            return [
                "event" => $trigger->getEventName(),
            ];
        }, function (array $data) {
            return EventTrigger::get($data["event"] ?? $data["key"]);
        });

        self::add(self::FORM, new FormTriggerForm(), function (FormTrigger $trigger) {
            return [
                "formName" => $trigger->getFormName(),
                "extraData" => $trigger->getExtraData(),
            ];
        }, function (array $data) {
            return new FormTrigger($data["formName"] ?? $data["key"], $data["extraData"] ?? $data["subKey"]);
        });

        self::add(self::TIME, new TimeTriggerForm(), function (TimeTrigger $trigger) {
            return [
                "hours" => $trigger->getHours(),
                "minutes" => $trigger->getMinutes(),
            ];
        }, function (array $data) {
            return new TimeTrigger((int)($data["hours"] ?? $data["key"]), (int)($data["minutes"] ?? $data["subKey"]));
        });

        self::add(self::CUSTOM, new CustomTriggerForm(), function (CustomTrigger $trigger) {
            return [
                "identifier" => $trigger->getIdentifier(),
            ];
        }, function (array $data) {
            return new CustomTrigger($data["identifier"] ?? $data["key"]);
        });
    }

    /**
     * @param string $type
     * @param TriggerForm $form
     * @param \Closure(Trigger $trigger): array $serializer
     * @param \Closure(array $data): Trigger $deserializer
     * @return void
     */
    public static function add(string $type, TriggerForm $form, \Closure $serializer, \Closure $deserializer): void {
        self::$forms[$type] = $form;
        self::$serializers[$type] = $serializer;
        self::$deserializers[$type] = $deserializer;
    }

    public static function serialize(Trigger $trigger): array {
        $serializer = self::$serializers[$trigger->getType()] ?? null;
        if ($serializer === null) {
            throw new \InvalidArgumentException("Trigger type ".$trigger->getType()." is not registered");
        }

        return [
            "type" => $trigger->getType(),
            "values" => $serializer($trigger),
        ];
    }

    public static function deserialize(array $data): ?Trigger {
        $type = $data["type"];
        $values = $data["values"] ?? [];
        if (isset($data["key"])) $values["key"] ??= $data["key"];
        if (isset($data["subKey"])) $values["subKey"] ??= $data["subKey"];

        $deserializer = self::$deserializers[$type] ?? null;
        if ($deserializer === null) {
            throw new \InvalidArgumentException("Trigger type ".$type." is not registered");
        }

        return $deserializer($values);
    }

    /**
     * @return TriggerForm[]
     */
    public static function getAllForm(): array {
        return self::$forms;
    }

    public static function getForm(string $type): ?TriggerForm {
        $form = self::$forms[$type] ?? null;
        if ($form === null) return null;
        return clone $form;
    }

    public static function existsForm(string $type): bool {
        return isset(self::$forms[$type]);
    }
}