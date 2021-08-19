<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\trigger\block\BlockTrigger;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\trigger\custom\CustomTrigger;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\trigger\time\TimeTrigger;
use aieuo\mineflow\ui\trigger\BlockTriggerForm;
use aieuo\mineflow\ui\trigger\CommandTriggerForm;
use aieuo\mineflow\ui\trigger\CustomTriggerForm;
use aieuo\mineflow\ui\trigger\EventTriggerForm;
use aieuo\mineflow\ui\trigger\FormTriggerForm;
use aieuo\mineflow\ui\trigger\TimeTriggerForm;
use aieuo\mineflow\ui\trigger\TriggerForm;

class Triggers {

    public const BLOCK = "block";
    public const COMMAND = "command";
    public const EVENT = "event";
    public const FORM = "form";
    public const TIME = "time";
    public const CUSTOM = "custom";

    /** @var TriggerForm[] */
    private static array $forms = [];
    /** @var string[] */
    private static array $list = [];

    public static function init(): void {
        self::add(self::BLOCK, BlockTrigger::class, new BlockTriggerForm());
        self::add(self::COMMAND, CommandTrigger::class, new CommandTriggerForm());
        self::add(self::EVENT, EventTrigger::class, new EventTriggerForm());
        self::add(self::FORM, FormTrigger::class, new FormTriggerForm());
        self::add(self::TIME, TimeTrigger::class, new TimeTriggerForm());
        self::add(self::CUSTOM, CustomTrigger::class, new CustomTriggerForm());
    }

    public static function add(string $type, string $class, TriggerForm $form): void {
        self::$list[$type] = $class;
        self::$forms[$type] = $form;
    }

    public static function getTrigger(string $type, string $key = "", string $subKey = ""): ?Trigger {
        $trigger = self::$list[$type] ?? null;
        if ($trigger === null) return null;

        /** @var Trigger $trigger */
        return $trigger::create($key, $subKey);
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